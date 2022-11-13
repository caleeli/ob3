<?php

class Balsamic
{
    private $configPage;
    private $name;

    public function __construct($sqlite_file, $name)
    {
        $this->name = $name;
        $this->sqlite_file = $sqlite_file;
        $this->preview = false;
        $this->withLayout = false;
        $this->windowWidth = 1182;
        $this->layoutLeft = 100;
        $this->layoutTop = 116;
        // open sqlite file and set utf8 encoding
        $this->connection = new PDO('sqlite:' . $sqlite_file);
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function toSvelte($filename)
    {
        $this->preview = true;
        ob_start();
        $this->printSvelteScreen();
        $svelteScreen = ob_get_clean();
        // save configPage.json
        $configPage = $this->getConfigPage();
        $configPageFilename = dirname($filename) . '/config.json';
        file_put_contents($configPageFilename, json_encode($configPage, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        // save page.svelte
        file_put_contents($filename, $svelteScreen);
        // run prettier at $_ENV['FRONTEND_HOME'];
        $cwdir = getcwd();
        chdir($_ENV['FRONTEND_HOME']);
        $command = 'npm run format-file -- --write ' . escapeshellarg($filename);
        $this->runWithNode($command, $_ENV['NODE_VERSION'] ?? '');
        chdir($cwdir);
    }

    private function getConfigPage()
    {
        return $this->configPage;
    }

    private function runWithNode($command, $nodeVersion)
    {
        //Load nvm
        if (!getenv('NVM_DIR')) {
            putenv('NVM_DIR=' . $_ENV['NVM_DIR']);
        }
        exec('$NVM_DIR/nvm.sh');
        $filenameRun = tempnam('/tmp', 'run');
        $nvmLoader = '[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"';
        if ($nodeVersion) {
            $setVersion = 'nvm install ' . $nodeVersion . ';nvm use ' . $nodeVersion;
        } else {
            $setVersion = '';
        }
        file_put_contents($filenameRun, "#!/bin/bash\n$nvmLoader\n{$setVersion}\n$command");
        chmod($filenameRun, 0777);
        exec("$filenameRun");
    }

    public function printSvelteScreen()
    {
        // Select all the rows in the RESOURCES table
        $statement = $this->connection->prepare('SELECT * FROM RESOURCES');
        $statement->execute();
        $resources = $statement->fetchAll(PDO::FETCH_ASSOC);

        $svelteScreen = new DOMDocument();
        // Add <script lang="ts"> to the document
        $script = $svelteScreen->createElement('script');
        $script->setAttribute('lang', 'ts');
        $svelteScreen->appendChild($script);
        $svelteScreen->appendChild($svelteScreen->createTextNode("\n"));
        // Config Object
        $this->configPage = new StdClass;

        foreach ($resources as $resource) {
            $id = $resource['ID'];
            $branch = $resource['BRANCHID'];
            $attributes = json_decode($resource['ATTRIBUTES']);
            $data = $resource['DATA'];
            if (substr($data, 0, 1) == '{') {
                $data = json_decode($data, true);
            }

            // Print resource
            $this->debug($id . ' - ' . $branch . "\n");
            // Add <!-- $id --> to the document
            $svelteScreen->appendChild($svelteScreen->createTextNode("\n"));
            $comment = $svelteScreen->createComment($id . ' - ' . $branch);
            $svelteScreen->appendChild($comment);
            $svelteScreen->appendChild($svelteScreen->createTextNode("\n"));

            // print_r($attributes);
            // print_r($data);
            if (is_array($data)) {
                $mockup = $data['mockup'];
                $controls = $mockup['controls'];
                $controlList = [];
                foreach ($controls['control'] as $control) {
                    $controlList[] = $control;
                }
                $controlList = $this->detectHorizontalMerges($controlList);
                foreach ($controlList as $control) {
                    $controlType = $control['typeID'];
                    // debug($controlType . ": " . json_encode($control, JSON_UNESCAPED_UNICODE));
                }
                $slots = $this->normalizeSizePosition($controlList);
                // debug(json_encode($slots, JSON_UNESCAPED_UNICODE));
                $this->slots2WebComponents($slots, $svelteScreen, $this->configPage);
            }
            // only first resource
            if ($this->preview) {
                break;
            }
        }

        if ($this->preview) {
            $html = $svelteScreen->saveHTML();
            // decode htmlentities
            $html = html_entity_decode($html);
            echo $html;
        }
    }
    public function debug(...$params)
    {
        if ($this->preview) {
            return;
        }
        foreach ($params as $param) {
            $isComplex = is_array($param) || is_object($param);
            print_r($param);
        }
        if (!$isComplex) {
            echo "\n";
        }
    }

    ////
    public function horizontalMerge($left, $right)
    {
        switch ($left['typeID']) {
            case 'Icon':
                switch ($right['typeID']) {
                    case 'TextInput':
                        $merged = array_merge($left, $right);
                        if ($left['properties']['icon']['ID'] === 'upload') {
                            $merged['typeID'] = 'FileInput';
                        } else {
                            $merged['typeID'] = 'IconTextInput';
                        }
                        $merged['ID'] = $left['ID'];
                        $merged['measuredW'] = $right['measuredW'] + $left['measuredW'] - ($right['x'] - $left['x']);
                        $merged['x'] = $left['x'];
                        $merged['y'] = min($left['y'], $right['y']);
                        $merged['measuredH'] = max($left['y'] + $left['measuredH'], $right['y'] + $right['measuredH']) - $merged['y'];
                        $merged['w'] = $merged['measuredW'];
                        $merged['h'] = $merged['measuredH'];
                        $merged['properties'] = array_merge($left['properties'], $right['properties']);
                        return $merged;
                }
        }
    }

    public function detectHorizontalMerges($controls)
    {
        $maxDeltaX = 10;
        $maxDeltaY = 10;
        for ($i=0; $i<count($controls); $i++) {
            $left = $controls[$i];
            for ($j=0; $j<count($controls); $j++) {
                $right = $controls[$j];
                if ($left['ID'] == $right['ID']) {
                    continue;
                }
                $w = $left['w'] ?? $left['measuredW'] ?? 0;
                $deltaX = abs($right['x'] - ($left['x'] + $w));
                $deltaY = abs($right['y'] - $left['y']);
                if ($deltaX < $maxDeltaX && $deltaY < $maxDeltaY) {
                    $merged = $this->horizontalMerge($left, $right);
                    if ($merged) {
                        $controls[$i] = $merged;
                        array_splice($controls, $j, 1);
                        $j--;
                    }
                }
            }
        }
        return $controls;
    }

    public function normalizeSizePosition($controls)
    {
        $maxColumns = 2;
        $columnWidth = $this->windowWidth / $maxColumns;
        $rowHeight = 32;
        $slots = [[]];
        foreach ($controls as $i => $control) {
            $x = $control['x'] - $this->layoutLeft;
            $y = $control['y'] - $this->layoutTop;
            if ($x < 0 || $y < 0) {
                // skip controls outside the layout
                continue;
            }
            $w = $control['w'] ?? $control['measuredW'];
            $h = $control['h'] ?? $control['measuredH'];
            $column = floor($x / $columnWidth);
            $row = floor($y / $rowHeight);
            $controls[$i]['x'] = $x;
            $controls[$i]['y'] = $y;
            $controls[$i]['w'] = $w;
            $controls[$i]['h'] = $h;
            $controls[$i]['measuredW'] = $w;
            $controls[$i]['measuredH'] = $h;
            $controls[$i]['column'] = $column;
            $controls[$i]['columns'] = floor($w / $columnWidth);
            if (!isset($slots[$row])) {
                $slots[$row] = [];
            }
            // if (!isset($slots[$row][$column])) {
            //     $slots[$row][$column] = [];
            // }
            // $slots[$row][$column][] = $controls[$i];
            $slots[$row][] = $controls[$i];
        }
        // sort by key
        ksort($slots);
        $slots = array_values($slots);
        // sort $slots[$row][$column] by x
        foreach ($slots as $row => $columns) {
            // foreach ($columns as $column => $controls) {
            //     usort($controls, function ($a, $b) {
            //         return $a['x'] <= $b['x'];
            //     });
            //     $slots[$row][$column] = $controls;
            // }
            usort($columns, function ($a, $b) {
                return $a['x'] >= $b['x'];
            });
            $grouped = [];
            $colOffset = 0;
            foreach ($columns as $column) {
                $c = $column['column'] - $colOffset;
                $colOffset += $column['columns'];
                for ($i=0; $i <=$c; $i++) {
                    if (!isset($grouped[$i])) {
                        $grouped[$i] = [];
                    }
                }
                $grouped[$c][] = $column;
            }
            for ($i = $colOffset + 1; $i < $maxColumns; $i++) {
                $grouped[] = [];
            }
            $slots[$row] = $grouped;
        }
        return $slots;
    }

    public function slots2WebComponents($rows, DomDocument $svelteScreen, $configObject)
    {
        foreach ($rows as $row) {
            $divRow = $svelteScreen->createElement('div');
            $divRow->setAttribute('class', 'row');
            foreach ($row as $column) {
                $divCol = $svelteScreen->createElement('div');
                $divCol->setAttribute('class', 'cell');
                $divRow->appendChild($divCol);
                foreach ($column as $control) {
                    $controlType = $control['typeID'];
                    $this->debug($controlType);
                    $controlTypeFn = $controlType . 'Component';
                    if (method_exists($this, $controlType)) {
                        $this->$controlType($control, $control['properties'], $divCol, $configObject);
                    } elseif (method_exists($this, $controlTypeFn)) {
                        $this->$controlTypeFn($control, $control['properties'], $divCol, $configObject);
                    }
                    $divCol->appendChild($svelteScreen->createTextNode("\n"));
                }
            }
            $svelteScreen->appendChild($divRow);
            // $svelteScreen->appendChild($svelteScreen->createTextNode("\n"));
        }
    }


    ////
    public function AppBar($controlPosition, $controlProperties, DOMElement $svelteScreen)
    {
        if (!$this->withLayout) {
            return;
        }
        $node = $svelteScreen->ownerDocument->createElement('AppBar');

        $svelteScreen->appendChild($node);
    }

    ////
    public function Subtitle($controlPosition, $controlProperties, DOMElement $svelteScreen)
    {
        $node = $svelteScreen->ownerDocument->createElement('h2');
        $node->nodeValue = '{__(' . json_encode($controlProperties['text'], JSON_UNESCAPED_UNICODE) . ')}';
        $svelteScreen->appendChild($node);
    }

    ////
    public function ListComponent($controlPosition, $controlProperties, DOMElement $svelteScreen)
    {
        // if x < 10 => Menu
        $isMenu = $controlPosition['x'] < 10;
        if ($isMenu) {
            if (!$this->withLayout) {
                return;
            }
            $node = $svelteScreen->ownerDocument->createElement('Menu');
            $svelteScreen->appendChild($node);
        }
    }

    ////
    public function FileInput($controlPosition, $controlProperties, DOMElement $svelteScreen)
    {
        $node = $svelteScreen->ownerDocument->createElement('FileInput');
        // bind value
        $name = $controlProperties['text'];
        $name = preg_replace('/[^a-zA-Z0-9]+/', '_', $name);
        $label = '{__(' . json_encode($controlProperties['text'], JSON_UNESCAPED_UNICODE) . ')}';
        $node->setAttribute('bind:value', '{'.$name.'}');
        $node->setAttribute('placeholder', $label);
        $node->setAttribute('store', '{fileStore}');
        $svelteScreen->appendChild($node);
        // add code to script
        $script = $svelteScreen->ownerDocument->getElementsByTagName('script')->item(0);
        $this->addUniqueScriptCode($script, 'import { translation as __ } from "$lib/translations";');
        $this->addUniqueScriptCode($script, 'import FileInput from "$lib/FileInput.svelte";');
        $this->addUniqueScriptCode($script, 'import fileStore from "$lib/FileStore";');
        $this->addUniqueScriptCode($script, "let {$name}:{ filename: null };");
    }

    public function addUniqueScriptCode(DOMElement $script, $line)
    {
        $lines = explode("\n", $script->nodeValue);
        if (!in_array($line, $lines)) {
            $lines[] = $line;
        }
        // order imports
        $imports = [];
        $rest = [];
        foreach ($lines as $line) {
            if (preg_match('/^import/', $line)) {
                $imports[] = $line;
            } else {
                $rest[] = $line;
            }
        }
        sort($imports);
        $script->nodeValue = implode("\n", array_merge($imports, $rest));
    }

    ////
    public function Button($controlPosition, $controlProperties, DOMElement $svelteScreen)
    {
        $node = $svelteScreen->ownerDocument->createElement('Button');
        $node->nodeValue = $controlProperties['text'];
        $svelteScreen->appendChild($node);
        // add code to script
        $script = $svelteScreen->ownerDocument->getElementsByTagName('script')->item(0);
        $this->addUniqueScriptCode($script, 'import { Button } from "fluent-svelte";');
    }

    ////
    public function TextInput($controlPosition, $controlProperties, DOMElement $svelteScreen)
    {
        $node = $svelteScreen->ownerDocument->createElement('TextBox');
        $name = $controlProperties['text'];
        $name = preg_replace('/[^a-zA-Z0-9]+/', '_', $name);
        $label = '{__(' . json_encode($controlProperties['text'], JSON_UNESCAPED_UNICODE) . ')}';
        $svelteScreen->appendChild($node);
        $node->setAttribute('bind:value', '{'.$name.'}');
        $node->setAttribute('placeholder', $label);
        // add code to script
        $script = $svelteScreen->ownerDocument->getElementsByTagName('script')->item(0);
        $this->addUniqueScriptCode($script, 'import { TextBox } from "fluent-svelte";');
        $this->addUniqueScriptCode($script, "let {$name}:string;");
    }

    ////
    public function DataGrid($control, $controlProperties, DOMElement $svelteScreen, $configObject)
    {
        $this->debug($controlProperties);
        $node = $svelteScreen->ownerDocument->createElement('Grid');
        $svelteScreen->appendChild($node);
        // parse balsamic datagrid properties
        $table = explode("\n", $controlProperties['text']);
        // get headers
        $headers = [];
        $headersLine = explode(",", $table[0]);
        $firstRow = explode(",", $table[1]);
        foreach ($headersLine as $i => $header) {
            $row = $firstRow[$i];
            $isActions = preg_match('/^Actions|^Acciones/', $header);
            $field = $this->convertLabel2Variable($header);
            if ($isActions) {
                // parse actions: [action] [action] ...
                $actions = preg_match_all('/\[(.*?)\]/', $row, $matches);
                $actions = $matches[1];
                $headers[] = [
                    'label' => $header,
                    'value' => json_encode($actions, JSON_UNESCAPED_UNICODE),
                    'control' => 'actions',
                    'align' => 'center',
                ];
            } else {
                $headers[] = [
                    'label' => $header,
                    'value' => $field,
                    'sortable' => true,
                    'field' => $field,
                    'align' => 'left',
                ];
            }
        }
        $config = [
            'headers' => $headers,
        ];
        $gridUniqueName = "grid_{$control['ID']}";
        $gridStoreUniqueName = "gridStore_{$control['ID']}";
        $configObject->$gridUniqueName = $config;
        $configObject->$gridStoreUniqueName = [
            'url' => 'users',
        ];
        //$node->setAttribute('config', '{' . json_encode($config, JSON_UNESCAPED_UNICODE) . '}');
        $node->setAttribute('config', '{new GridConfig(config.' . $gridUniqueName . ')}');
        $node->setAttribute('store', '{new ApiStore(config.' . $gridStoreUniqueName . ')}');
        $node->setAttribute('configStore', '{configStore}');
        // add code to script
        $script = $svelteScreen->ownerDocument->getElementsByTagName('script')->item(0);
        $this->addUniqueScriptCode($script, 'import Grid  from "$lib/Grid.svelte";');
        $this->addUniqueScriptCode($script, 'import ApiStore  from "$lib/ApiStore";');
        $this->addUniqueScriptCode($script, 'import config from "./config.json";');
        $this->addUniqueScriptCode($script, 'import ConfigStore from "$lib/ConfigStore";');
        $this->addUniqueScriptCode($script, 'import GridConfig from "$lib/GridConfig";');

        $this->addUniqueScriptCode($script, 'let configStore = new ConfigStore("' . $this->name . '", config);');
    }

    public function convertLabel2Variable($label)
    {
        // remove accents á -> é -> e ...
        $label = $this->normalize($label);
        // remove non alphanumeric characters
        $label = preg_replace('/[^a-zA-Z0-9]+/', '_', $label);
        // remove leading numbers
        $label = preg_replace('/^[0-9]+/', '', $label);
        // remove leading underscore
        $label = preg_replace('/^_/', '', $label);
        // remove trailing underscore
        $label = preg_replace('/_$/', '', $label);
        // lowercase
        $label = strtolower($label);
        return $label;
    }
    public function normalize($string)
    {
        $table = [
            'Š'=>'S', 'š'=>'s', 'Đ'=>'Dj', 'đ'=>'dj', 'Ž'=>'Z', 'ž'=>'z', 'Č'=>'C', 'č'=>'c', 'Ć'=>'C', 'ć'=>'c',
            'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
            'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
            'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss',
            'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e',
            'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o',
            'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b',
            'ÿ'=>'y', 'Ŕ'=>'R', 'ŕ'=>'r',
        ];

        return strtr($string, $table);
    }
}
