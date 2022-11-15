<?php

class Balsamic
{
    private $resources = [];
    private $configPage;
    private $name;
    private $layoutLeft = 0; // 100;
    private $layoutTop = 0; // 116;
    private $handlers = [];
    private $screenData = [];

    /**
     * @var DOMElement
     */
    private $popup;

    public function __construct($sqlite_file, $name)
    {
        $this->name = $name;
        $this->sqlite_file = $sqlite_file;
        $this->preview = false;
        $this->withLayout = false;
        $this->windowWidth = 1182;
        // open sqlite file and set utf8 encoding
        $this->connection = new PDO('sqlite:' . $sqlite_file);
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function getWireframesList()
    {
        $statement = $this->connection->prepare('SELECT * FROM RESOURCES');
        $statement->execute();
        $resources = $statement->fetchAll(PDO::FETCH_ASSOC);
        $wireframes = [];
        foreach ($resources as $resource) {
            $id = $resource['ID'];
            $branch = $resource['BRANCHID'];
            $attributes = json_decode($resource['ATTRIBUTES'], true);
            // skip trashed
            if ($attributes['trashed']) {
                continue;
            }
            if ($attributes['kind'] === 'mockup') {
                $wireframes[] = [
                    'id' => $id,
                    'branch' => $branch,
                    'name' => $attributes['name'],
                    'order' => $attributes['order'],
                ];
            }
        }
        // sort by order
        usort($wireframes, function ($a, $b) {
            return $a['order'] > $b['order'];
        });
        return $wireframes;
    }

    public function toSvelte($filename, array $selected)
    {
        $this->preview = true;
        ob_start();
        $this->printSvelteScreen($selected);
        $svelteScreen = ob_get_clean();
        // save/merge configPage.json
        $configPage = $this->getConfigPage();
        $configPage = json_decode(json_encode($configPage), true);
        $configPageFilename = dirname($filename) . '/config.json';
        // recursive merge
        if (file_exists($configPageFilename)) {
            $configPage = $this->merge($configPage, json_decode(file_get_contents($configPageFilename), true));
        }
        error_log($configPageFilename);
        file_put_contents($configPageFilename, json_encode($configPage, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        // create default empty handler.ts
        $handlerFilename = dirname($filename) . '/handler.ts';
        if (!file_exists($handlerFilename)) {
            file_put_contents($handlerFilename, 'export default function handler(action: string, data: any) { console.log(action, data); }');
        }
        // save page.svelte
        file_put_contents($filename, $svelteScreen);
        // run prettier at $_ENV['FRONTEND_HOME'];
        $cwdir = getcwd();
        chdir($_ENV['FRONTEND_HOME']);
        $command = 'npm run format-file -- --write ' . escapeshellarg($filename);
        $this->runWithNode($command, $_ENV['NODE_VERSION'] ?? '');
        chdir($cwdir);
    }

    private function merge($base, $previous)
    {
        if (is_array($previous)) {
            $sameSize = count($base) === count($previous);
            foreach ($previous as $key => $value) {
                if (!isset($base[$key])) {
                    continue;
                }
                if (is_numeric($key) && !$sameSize) {
                    continue;
                }
                if (is_array($value) && is_array($base[$key])) {
                    $base[$key] = $this->merge($base[$key], $value);
                } elseif (!is_array($value) && !is_array($base[$key])) {
                    $base[$key] = $value;
                }
            }
        }
        return $base;
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

    public function printSvelteScreen(array $selected)
    {
        // Select all the rows in the RESOURCES table
        $statement = $this->connection->prepare('SELECT * FROM RESOURCES');
        $statement->execute();
        $resources = $statement->fetchAll(PDO::FETCH_ASSOC);
        $this->resources = $resources;

        $svelteScreen = new DOMDocument();
        // Add <script lang="ts"> to the document
        $script = $svelteScreen->createElement('script');
        $script->setAttribute('lang', 'ts');
        $svelteScreen->appendChild($script);
        $svelteScreen->appendChild($svelteScreen->createTextNode("\n"));
        // Config Object
        $this->configPage = new StdClass;

        $isPopup = false;
        foreach ($resources as $resource) {
            if ($selected && !in_array($resource['ID'], $selected)) {
                continue;
            }
            $id = $resource['ID'];
            $attributes = json_decode($resource['ATTRIBUTES'], true);
            $name = $attributes['name'];
            $data = $resource['DATA'];
            if (substr($data, 0, 1) == '{') {
                $data = json_decode($data, true);
            }

            // Print resource
            $this->debug($id . ' - ' . $name . "\n");
            // Add <!-- $id --> to the document
            $svelteScreen->appendChild($svelteScreen->createTextNode("\n"));
            $comment = $svelteScreen->createComment($id . ' - ' . $name);
            $svelteScreen->appendChild($comment);
            $svelteScreen->appendChild($svelteScreen->createTextNode("\n"));

            // print_r($attributes);
            // print_r($data);
            if (is_array($data)) {
                $mockup = $data['mockup'];
                $controls = $mockup['controls'];
                $controlList = [];
                $enabled = !$isPopup;
                foreach ($controls['control'] as $control) {
                    // add reference to resource
                    $control['resourceID'] = $id;
                    if ($isPopup && !$enabled) {
                        if ($control['typeID'] === 'FieldSet') {
                            $enabled = true;
                        } else {
                            continue;
                        }
                    }
                    if (!isset($controlList[$control['ID']])) {
                        $controlList[$control['ID']] = $control;
                    }
                }
            }

            $controlList = array_values($controlList);
            $controlList = $this->detectHorizontalMerges($controlList);
            foreach ($controlList as $control) {
                $controlType = $control['typeID'];
                // debug($controlType . ": " . json_encode($control, JSON_UNESCAPED_UNICODE));
            }
            $slots = $this->normalizeSizePosition($controlList);
            // debug(json_encode($slots, JSON_UNESCAPED_UNICODE));
            $this->slots2WebComponents($slots, $svelteScreen, $this->configPage);

            // the next resources are popups
            $isPopup = true;
            // only first resource
            if (!$selected) {
                break;
            }
        }

        // add screen data
        $script = $svelteScreen->getElementsByTagName('script')->item(0);
        $script->appendChild($svelteScreen->createTextNode("\n"));
        $data = "let data = {\n";
        foreach ($this->screenData as $key => $value) {
            $data .= "\t" . $key . ': ' . $value . ",\n";
        }
        $data .= "};\n";
        $script->appendChild($svelteScreen->createTextNode($data));

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
        $rowHeight = 24;
        $slots = [[]];
        foreach ($controls as $i => $control) {
            $x = $control['x'] - $this->layoutLeft;
            $y = $control['y'] - $this->layoutTop;
            if ($x < 0 || $y < 0) {
                // skip controls outside the layout
                error_log("Control outside the layout: " . json_encode($control));
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
        $this->popup = null;
        foreach ($rows as $row) {
            $divRow = $svelteScreen->createElement('div');
            $divRow->setAttribute('class', 'row');
            if (!$this->popup) {
                $root = $svelteScreen;
            } else {
                $root = $this->popup;
            }
            $root->appendChild($divRow);
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
                    } else {
                        error_log('Unknown control type: ' . $controlType);
                    }
                    $divCol->appendChild($svelteScreen->createTextNode("\n"));
                }
            }
            $root->appendChild($svelteScreen->createTextNode("\n"));
        }
    }

    private function getResourceById($id)
    {
        foreach ($this->resources as $resource) {
            if ($resource['ID'] == $id) {
                return $resource;
            }
        }
        return null;
    }

    private function getResourceName($id)
    {
        $resource = $this->getResourceById($id);
        if ($resource) {
            $attributes = json_decode($resource['ATTRIBUTES'], true);
            return $attributes['name'];
        }
        return null;
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
        $node->setAttribute('bind:value', '{data.'.$name.'}');
        $node->setAttribute('placeholder', $label);
        $node->setAttribute('store', '{fileStore}');
        $svelteScreen->appendChild($node);
        // add code to script
        $script = $svelteScreen->ownerDocument->getElementsByTagName('script')->item(0);
        $this->addUniqueScriptCode($script, 'import { translation as __ } from "$lib/translations";');
        $this->addUniqueScriptCode($script, 'import FileInput from "$lib/FileInput.svelte";');
        $this->addUniqueScriptCode($script, 'import fileStore from "$lib/FileStore";');
        // $this->addUniqueScriptCode($script, "let {$name}:{ filename: null };");
        $this->addScreenData($name, '{ filename: null }');
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
        $node->setAttribute('bind:value', '{data.'.$name.'}');
        $node->setAttribute('placeholder', $label);
        // add code to script
        $script = $svelteScreen->ownerDocument->getElementsByTagName('script')->item(0);
        $this->addUniqueScriptCode($script, 'import { TextBox } from "fluent-svelte";');
        // $this->addUniqueScriptCode($script, "let {$name}:string;");
        $this->addScreenData($name, '""');
    }

    ////
    public function DataGrid($control, $controlProperties, DOMElement $svelteScreen, $configObject)
    {
        $this->debug($controlProperties);
        // names and IDs
        $gridUniqueName = "grid{$control['ID']}";
        $gridStoreUniqueName = "gridStore{$control['ID']}";

        $node = $svelteScreen->ownerDocument->createElement('Grid');
        $svelteScreen->appendChild($node);
        // parse balsamic datagrid properties
        $table = explode("\n", $controlProperties['text']);
        // get headers
        $headers = [];
        $actionTriggers = [];
        $headersLine = explode(",", $table[0]);
        $firstRow = explode(",", $table[1]);
        foreach ($headersLine as $i => $header) {
            $row = $firstRow[$i];
            // is sortable?
            $sortable = preg_match('/\s+\^$|\s+\^v$|\s+v$/', $header, $match) > 0;
            if ($sortable) {
                $header = substr($header, 0, -strlen($match[0]));
            }
            $header = trim($header);
            $isActions = preg_match('/^Actions|^Acciones/', $header);
            $field = $this->convertLabel2Variable($header);
            if ($isActions) {
                // parse actions: [action] [action] ...
                $actions = preg_match_all('/\[(.*?)\](?:\(([\w-]+)\))?/', $row, $matches);
                $actions = $matches[1];
                $links = $matches[2];
                // foreach ($links as $j => $link) {
                //     $action = $actions[$j];
                //     if ($link) {
                //         $resourceName = $this->getPopupNameFor($link);
                //         $linkHandlerName = $gridUniqueName . ucfirst($this->convertLabel2Variable($link));
                //         $this->addOpenLinkAction($linkHandlerName, $resourceName);
                //         $actionTriggers["on:$action"] = '{(e) => data = handler("' . $linkHandlerName . '", e.detail, data)}';
                //     }
                // }
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
                    'sortable' => $sortable,
                ];
            }
        }
        $config = [
            'headers' => $headers,
        ];
        $configObject->$gridUniqueName = $config;
        $configObject->$gridStoreUniqueName = [
            'url' => 'users',
        ];
        //$node->setAttribute('config', '{' . json_encode($config, JSON_UNESCAPED_UNICODE) . '}');
        $node->setAttribute('config', '{new GridConfig(config.' . $gridUniqueName . ')}');
        $node->setAttribute('store', '{new ApiStore(config.' . $gridStoreUniqueName . ')}');
        $node->setAttribute('configStore', '{configStore}');
        $node->setAttribute('on:action', '{(e) => data = handler(e.detail.action, e.detail, data)}');
        // foreach($actionTriggers as $event => $handler) {
        //     $node->setAttribute($event, $handler);
        // }
        // add code to script
        $script = $svelteScreen->ownerDocument->getElementsByTagName('script')->item(0);
        $this->addUniqueScriptCode($script, 'import Grid  from "$lib/Grid.svelte";');
        $this->addUniqueScriptCode($script, 'import ApiStore  from "$lib/ApiStore";');
        $this->addUniqueScriptCode($script, 'import config from "./config.json";');
        $this->addUniqueScriptCode($script, 'import ConfigStore from "$lib/ConfigStore";');
        $this->addUniqueScriptCode($script, 'import GridConfig from "$lib/GridConfig";');
        $this->addUniqueScriptCode($script, 'import handler from "./handler";');
        $this->addUniqueScriptCode($script, 'let configStore = new ConfigStore("' . $this->name . '", config);');
    }

    private function addOpenLinkAction($handlerName, $popupName)
    {
        $this->handlers[$handlerName] = [
            'name' => $handlerName,
            'code' => "openPopup('{$popupName}');",
        ];
    }

    private function addScreenData($name, $value)
    {
        $this->screenData[$name] = $value;
    }

    private function ComboBox($control, $controlProperties, DOMElement $svelteScreen, $configObject)
    {
        // error_log(json_encode($control));
        $node = $svelteScreen->ownerDocument->createElement('ComboBox');
        $svelteScreen->appendChild($node);
        $name = $this->convertLabel2Variable($controlProperties['text']);

        // add code to script
        $script = $svelteScreen->ownerDocument->getElementsByTagName('script')->item(0);
        $this->addUniqueScriptCode($script, 'import ComboBox from "$lib/ComboBox.svelte";');
    }

    private function Label($control, $controlProperties, DOMElement $svelteScreen, $configObject)
    {
        // error_log(json_encode($control));
        $node = $svelteScreen->ownerDocument->createElement('label');
        $node->nodeValue = $controlProperties['text'];
        $svelteScreen->appendChild($node);
    }

    // <ContentDialog bind:open={confirmDelete} title={__('Delete')} size="max">
    public function FieldSet($control, $controlProperties, DOMElement $div, $configObject)
    {
        // error_log(json_encode($control));
        $node = $div->ownerDocument->createElement('ContentDialog');
        $label = $controlProperties['text'];
        $name = $this->getPopupNameFor($control['resourceID']);
        $node->setAttribute('bind:open', '{data.' . $name . '.open}');
        $node->setAttribute('title', '{__(' . json_encode($label, JSON_UNESCAPED_UNICODE) . ')}');
        $node->setAttribute('size', 'max');
        $node->appendChild($div->ownerDocument->createTextNode("\n"));
        $screen = $div->parentNode->parentNode;
        $screen->appendChild($node);
        $screen->removeChild($div->parentNode);
        // add code to script
        $script = $div->ownerDocument->getElementsByTagName('script')->item(0);
        $this->addUniqueScriptCode($script, 'import { ContentDialog } from "fluent-svelte";');
        // $this->addUniqueScriptCode($script, 'let ' . $name . ' = {open: false};');
        $this->addScreenData($name, json_encode(['open' => false]));
        // move children to dialog
        $this->popup = $node;
    }

    private function getPopupNameFor($resourceID)
    {
        $name = $this->getResourceName($resourceID);
        $name = 'popup' . ucfirst($this->convertLabel2Variable($name));
        return $name;
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
        // camel case
        $label = preg_replace_callback('/_([a-z])/', function ($matches) {
            return strtoupper($matches[1]);
        }, $label);
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
