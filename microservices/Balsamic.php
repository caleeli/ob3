<?php

class Balsamic
{
    private $resources = [];
    private $comments = [];
    private $configPage;
    private $name;
    private $windowWidth;
    private $layoutLeft = 0; // 100;
    private $layoutTop = 0; // 116;
    private $handlers = [];
    private $screenData = [];
    private $maxColumns = 128;
    private $prefix = '';

    /**
     * @var DOMElement
     */
    private $popup;
    private $popupControl;

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
        file_put_contents($configPageFilename, json_encode($configPage, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        // create default empty handler.ts
        $handlerFilename = dirname($filename) . '/handler.ts';
        if (!file_exists($handlerFilename)) {
            file_put_contents($handlerFilename, 'export default function handler(action: string, detail: any, data: any): any { console.log(action, data); }');
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
        // sort resources by attributes order if exists
        usort($this->resources, function ($a, $b) {
            $a = json_decode($a['ATTRIBUTES'], true);
            $b = json_decode($b['ATTRIBUTES'], true);
            if (!isset($a['order'])) {
                return false;
            }
            if (!isset($b['order'])) {
                return false;
            }
            return $a['order'] > $b['order'];
        });
        // Select all the comments in the COMMENTS table with data different from `{}`
        $statement = $this->connection->prepare('SELECT * FROM COMMENTS WHERE DATA != "{}"');
        $statement->execute();
        $comments = $statement->fetchAll(PDO::FETCH_ASSOC);
        $this->comments = $comments;

        $svelteScreen = new DOMDocument();
        // Add <script lang="ts"> to the document
        $script = $svelteScreen->createElement('script');
        $script->setAttribute('lang', 'ts');
        $svelteScreen->appendChild($script);
        $svelteScreen->appendChild($svelteScreen->createTextNode("\n"));
        // Config Object
        $this->configPage = new StdClass;

        $isPopup = false;
        $this->prefix = '';
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
                $minIndex = 0;
                $resourceControls = $controls['control'];
                // sort by zOrder
                usort($resourceControls, function ($a, $b) {
                    return ($a['zOrder'] * 1) > ($b['zOrder'] * 1);
                });
                $resourceControls = $this->unGroupControls($resourceControls, 0, 0, '');
                // Find last FieldSet or Alert box
                foreach ($resourceControls as $index => $control) {
                    if ($control['typeID'] === 'FieldSet' || $control['typeID'] === 'Alert') {
                        $isPopup = true;
                        $popupName = $name;
                        $this->prefix = $this->convertLabel2Variable($popupName);
                        $minIndex = $index; // include FieldSet or Alert box
                    }
                }
                foreach ($resourceControls as $index => $control) {
                    // add reference to resource
                    $control['resourceID'] = $id;
                    if ($isPopup && $index < $minIndex) {
                        continue;
                    }
                    if (!isset($controlList[$control['ID']])) {
                        $controlList[$control['ID']] = $control;
                    }
                }
            }

            $controlList = array_values($controlList);
            $controlList = $this->detectHorizontalMerges($controlList);
            $slots = $this->normalizeSizePosition($controlList, $isPopup);
            // debug(json_encode($slots, JSON_UNESCAPED_UNICODE));
            $this->slots2WebComponents($slots, $svelteScreen, $this->configPage);

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
    // DE0DEDAD-A21A-430A-8F31-63E242CA5983
    public function commentAt($control, $default)
    {
        $resourceId = $control['resourceID'];
        $x = $control['originalX'];
        $y = $control['originalY'];
        $width = $control['w'];
        $height = $control['h'];
        $comments = $this->comments;
        foreach ($comments as $comment) {
            if ($comment['RESOURCEID'] !== $resourceId) {
                continue;
            }
            $commentData = json_decode($comment['DATA'], true);
            $commentX = $commentData['callouts'][0]['x'];
            $commentY = $commentData['callouts'][0]['y'] + 20;
            if ($commentX >= $x && $commentX <= $x + $width && $commentY >= $y && $commentY <= $y + $height) {
                preg_match('/^\[\(\d+\)\]\([\w-]+\)\s(.+)$/', $commentData['text'], $matches);
                if (count($matches) > 1) {
                    return trim($matches[1]);
                }
            }
        }
        return $default;
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
                        $rightW = $right['w'] ?? $right['measuredW'];
                        $leftW = $left['w'] ?? $left['measuredW'];
                        $rightH = $right['h'] ?? $right['measuredH'];
                        $leftH = $left['h'] ?? $left['measuredH'];
                        $merged['measuredW'] = $rightW + $leftW - ($right['x'] - $left['x']);
                        $merged['x'] = $left['x'];
                        $merged['y'] = min($left['y'], $right['y']);
                        $merged['measuredH'] = max($left['y'] + $leftH, $right['y'] + $rightH) - $merged['y'];
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

    public function normalizeSizePosition($controls, $isPopup)
    {
        if (!$isPopup) {
            // Find BrowserWindow
            foreach ($controls as $i => $control) {
                if ($control['typeID'] === 'BrowserWindow') {
                    $this->windowWidth = $control['w'];
                    break;
                }
            }
            // Find Left Menu
            foreach ($controls as $i => $control) {
                if ($control['typeID'] === 'List' && $control['x'] < 10) {
                    $this->layoutLeft = $control['x'] + $control['w'];
                    $this->layoutTop = $control['y'];
                    $this->windowWidth -= $this->layoutLeft;
                    break;
                }
            }
            $windowWidth = $this->windowWidth;
        } else {
            // Find last FieldSet or Alert box
            foreach ($controls as $i => $control) {
                if ($control['typeID'] === 'FieldSet' || $control['typeID'] === 'Alert') {
                    $this->layoutLeft = $control['x'];
                    $this->layoutTop = $control['y'];
                    $control['w'] = $control['w'] ?? $control['measuredW'];
                    $windowWidth = $control['w'];
                }
            }
        }
        // Process controls
        $columnWidth = $windowWidth / $this->maxColumns;
        $rowHeight = 16;
        $slots = [[]];
        $zoneWidth = $windowWidth / 3;
        foreach ($controls as $i => $control) {
            $w = $control['w'] ?? $control['measuredW'];
            $h = $control['h'] ?? $control['measuredH'];
            $x = $control['x'] - $this->layoutLeft;
            if ($control['typeID'] === 'FieldSet') {
                $y = $control['y'] - $this->layoutTop;
            } else {
                $y = $control['y'] + min($h, 28) / 2 - $this->layoutTop;
            }
            // FieldSet could be outside the layout
            if ($control['typeID'] !== 'FieldSet' && ($x < 0 || $y < 0)) {
                // skip controls outside the layout
                error_log("Control outside the layout: " . json_encode($control));
                continue;
            }
            $column = floor($x / $columnWidth);
            // $column = max(0, round($x / $columnWidth) -1);
            $row = floor($y / $rowHeight);
            $controls[$i]['originalX'] = $controls[$i]['x'];
            $controls[$i]['originalY'] = $controls[$i]['y'];
            $controls[$i]['horizontalZone'] = min(max(0, floor($x / $zoneWidth)), 2);
            $controls[$i]['x'] = $x;
            $controls[$i]['y'] = $y;
            $controls[$i]['w'] = $w;
            $controls[$i]['h'] = $h;
            $controls[$i]['measuredW'] = $w;
            $controls[$i]['measuredH'] = $h;
            $controls[$i]['column'] = $column;
            $controls[$i]['columns'] = floor(($x + $w) / $columnWidth) - $column;
            if (!isset($slots[$row])) {
                $slots[$row] = [];
            }
            $slots[$row][] = $controls[$i];
        }
        // sort by key
        ksort($slots);
        $slots = array_values($slots);
        foreach ($slots as $row => $controls) {
            // sort $slots[$row][$column] by x
            usort($controls, function ($a, $b) {
                return $a['x'] >= $b['x'];
            });
            $grouped = [[]];
            $c = 0;
            $c_max = 0;
            foreach ($controls as $control) {
                if ($control['column'] > $c_max) {
                    // add empty columns to $grouped
                    for ($i=$c_max; $i<$control['column']; $i++) {
                        $grouped[] = [];
                    }
                    $c = $control['column'];
                    $c_max = $c + $control['columns'];
                }
                if ($control['column'] >= $c && $control['column'] <= $c_max) {
                    $c_max = max($c_max, $control['column'] + $control['columns']);
                    $g = count($grouped) -1;
                    $grouped[$g][] = $control;
                }
            }
            // add empty columns to complete `$this->maxColumns`
            for ($i=$c_max; $i<$this->maxColumns - 1; $i++) {
                $grouped[] = [];
            }
            $dbg = [];
            foreach ($grouped as $g) {
                $text = '';
                foreach ($g as $control) {
                    $text .= ($control['properties']['text'] ?? $control['typeID']) . $control['column'] . '+' . $control['columns'] . ',';
                }
                $dbg[] = $text;
            }
            // error_log("Row max $c_max: " . implode(' | ', $dbg));
            $slots[$row] = $grouped;
        }
        return $slots;
    }

    private function unGroupControls($controls, $offsetX, $offsetY, $offsetID)
    {
        $response = [];
        foreach ($controls as $control) {
            $isGroup = $control['typeID'] === '__group__';
            if ($isGroup) {
                // un-group children controls
                $childrenControls = $control['children']['controls']['control'];
                $childrenControls = $this->unGroupControls($childrenControls, $control['x'], $control['y'], $control['ID'] . '_');
                array_push($response, ...$childrenControls);
            } else {
                $control['x'] += $offsetX;
                $control['y'] += $offsetY;
                $control['ID'] = $offsetID . $control['ID'];
                $response[] = $control;
            }
        }
        return $response;
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
            $merged = 0;
            if ($this->popup) {
                $maxColumns = $this->popupControl['columns'] + 1;
            } else {
                $maxColumns = $this->maxColumns;
            }
            foreach ($row as $column) {
                $divCol = $svelteScreen->createElement('div');
                $divCol->setAttribute('class', 'cell');
                // find lowest value of column
                if (count($column) === 0) {
                    $merged++;
                    continue;
                } elseif ($merged) {
                    $divColM = $svelteScreen->createElement('div');
                    $divColM->setAttribute('class', 'cell');
                    $divColM->setAttribute('style', 'width: ' . ($merged * 100 / $maxColumns) . '%');
                    $divRow->appendChild($divColM);
                }
                $merged = 0;
                $divRow->appendChild($divCol);
                $cMin = $this->maxColumns;
                $cMax = 0;
                foreach ($column as $control) {
                    $controlType = $control['typeID'];
                    $this->debug($controlType);
                    $controlTypeFn = $controlType . 'Component';
                    if (method_exists($this, $controlType)) {
                        $this->$controlType($control, $control['properties'] ?? [], $divCol, $configObject);
                    } elseif (method_exists($this, $controlTypeFn)) {
                        $this->$controlTypeFn($control, $control['properties'] ?? [], $divCol, $configObject);
                    } else {
                        error_log('Unknown control type: ' . $controlType);
                    }
                    $divCol->appendChild($svelteScreen->createTextNode("\n"));
                    $cMin = min($cMin, $control['column']);
                    $cMax = max($cMin, $control['column'] + $control['columns']);
                }
                $style = $divCol->getAttribute('style');
                $divCol->setAttribute('style', 'width: ' . (($cMax - $cMin + 1) * 100 / $maxColumns) . '%;' . $style);
            }
            if ($merged) {
                $divColM = $svelteScreen->createElement('div');
                $divColM->setAttribute('class', 'cell');
                $style = $divCol->getAttribute('style');
                $divColM->setAttribute('style', 'width: ' . ($merged * 100 / $maxColumns) . '%;' . $style);
                // $divColM->setAttribute('columnWidth', "100 * $merged / $maxColumns");
                $divRow->appendChild($divColM);
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
        // add code to script
        $script = $svelteScreen->ownerDocument->getElementsByTagName('script')->item(0);
        $this->addUniqueScriptCode($script, 'import { translation as __ } from "$lib/translations";');
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
    public function FileInput($control, $controlProperties, DOMElement $svelteScreen)
    {
        $node = $svelteScreen->ownerDocument->createElement('FileInput');
        // bind value
        $name = $controlProperties['text'];
        $name = $this->commentAt($control, $name);
        $name = preg_replace('/[^a-zA-Z0-9]+/', '_', $name);
        $node->setAttribute('bind:value', '{data.'.$name.'}');
        $placeholder = '{__(' . json_encode($name, JSON_UNESCAPED_UNICODE) . ')}';
        $node->setAttribute('placeholder', $placeholder);
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
    public function Button($control, $controlProperties, DOMElement $svelteScreen)
    {
        $node = $svelteScreen->ownerDocument->createElement('Button');
        $node->nodeValue = "\n{__(" . json_encode($controlProperties['text'], JSON_UNESCAPED_UNICODE) . ", data)}\n";
        // color
        if (isset($controlProperties['color'])) {
            $node->setAttribute('variant', 'accent');
        }
        // if horizontalZone == 2 => align to right
        if ($control['horizontalZone'] == 2) {
            $parentStyle = $svelteScreen->getAttribute('style') ?? '';
            // find justify-content
            $parentStyle = preg_replace('/\s*justify-content:\s*[^;]+;/', '', $parentStyle);
            $svelteScreen->setAttribute('style', $parentStyle . '; justify-content: flex-end;');
        }
        // if horizontalZone == 1 => align to center
        if ($control['horizontalZone'] == 1) {
            $parentStyle = $svelteScreen->getAttribute('style') ?? '';
            // find justify-content
            $parentStyle = preg_replace('/\s*justify-content:\s*[^;]+;/', '', $parentStyle);
            $svelteScreen->setAttribute('style', $parentStyle . '; justify-content: center;');
        }
        // add handler
        if ($this->prefix) {
            $handlerName = $this->prefix . ucfirst($this->convertLabel2Variable($controlProperties['text']) . 'Handler');
        } else {
            $handlerName = $this->convertLabel2Variable($controlProperties['text']) . 'Handler';
        }
        $handlerCode = 'Object.assign(data, handler(' . json_encode($handlerName) . ', e.detail, data))';
        $link = $controlProperties['href']['ID'] ?? null;
        if ($link) {
            $resourceName = $this->getPopupNameFor($link);
            $this->addHandler($handlerName, "");
            $handlerCode = 'data.'.$resourceName.'.open=true;' . $handlerCode;
        }
        $node->setAttribute('on:click', "{(e) => { $handlerCode }}");

        $svelteScreen->appendChild($node);
        // add code to script
        $script = $svelteScreen->ownerDocument->getElementsByTagName('script')->item(0);
        $this->addUniqueScriptCode($script, 'import { Button } from "fluent-svelte";');
        $this->addUniqueScriptCode($script, 'import handler from "./handler";');
    }

    ////
    public function TextInput($control, $controlProperties, DOMElement $svelteScreen)
    {
        // error_log(json_encode($controlProperties, JSON_PRETTY_PRINT));
        $node = $svelteScreen->ownerDocument->createElement('TextBox');
        $name = $this->convertLabel2Variable($controlProperties['text']);
        $name = $this->commentAt($control, $name);
        $name = preg_replace('/[^a-zA-Z0-9]+/', '_', $name);
        $svelteScreen->appendChild($node);
        $node->setAttribute('bind:value', '{data.'.$name.'}');
        $placeholder = '{__(' . json_encode($name, JSON_UNESCAPED_UNICODE) . ')}';
        $node->setAttribute('placeholder', $placeholder);
        // state disabled
        if (isset($controlProperties['state']) && $controlProperties['state'] === 'disabled') {
            $node->setAttribute('disabled', '{true}');
        }
        // add code to script
        $script = $svelteScreen->ownerDocument->getElementsByTagName('script')->item(0);
        $this->addUniqueScriptCode($script, 'import { TextBox } from "fluent-svelte";');
        $this->addScreenData($name, '""');
    }

    public function DateChooser($control, $controlProperties, DOMElement $svelteScreen)
    {
        // error_log(json_encode($controlProperties, JSON_PRETTY_PRINT));
        $node = $svelteScreen->ownerDocument->createElement('TextBox');
        $name = $this->convertLabel2Variable($controlProperties['text']);
        $name = $this->commentAt($control, $name);
        $name = preg_replace('/[^a-zA-Z0-9]+/', '_', $name);
        // $label = '{__(' . json_encode($controlProperties['text'], JSON_UNESCAPED_UNICODE) . ')}';
        $svelteScreen->appendChild($node);
        $node->setAttribute('bind:value', '{data.'.$name.'}');
        $node->setAttribute('type', 'date');
        $placeholder = '{__(' . json_encode($name, JSON_UNESCAPED_UNICODE) . ')}';
        $node->setAttribute('placeholder', $placeholder);
        // state disabled
        if (isset($controlProperties['state']) && $controlProperties['state'] === 'disabled') {
            $node->setAttribute('disabled', '{true}');
        }
        // add code to script
        $script = $svelteScreen->ownerDocument->getElementsByTagName('script')->item(0);
        $this->addUniqueScriptCode($script, 'import { TextBox } from "fluent-svelte";');
        $this->addScreenData($name, '""');
    }

    public function TextArea($control, $controlProperties, DOMElement $svelteScreen)
    {
        $node = $svelteScreen->ownerDocument->createElement('TextArea');
        $name = $controlProperties['text'];
        $name = $this->commentAt($control, $name);
        $name = $this->convertLabel2Variable($name);
        $svelteScreen->appendChild($node);
        $node->setAttribute('bind:value', '{data.'.$name.'}');
        $placeholder = '{__(' . json_encode($name, JSON_UNESCAPED_UNICODE) . ')}';
        $node->setAttribute('placeholder', $placeholder);
        $rows = round($control['h'] / 19);
        $node->setAttribute('rows', $rows);
        // state disabled
        if (isset($controlProperties['state']) && $controlProperties['state'] === 'disabled') {
            $node->setAttribute('disabled', '{true}');
        }
        // add code to script
        $script = $svelteScreen->ownerDocument->getElementsByTagName('script')->item(0);
        $this->addUniqueScriptCode($script, 'import TextArea from "$lib/TextArea.svelte";');
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
                foreach ($links as $j => $link) {
                    $action = $actions[$j];
                    if ($link) {
                        $resourceName = $this->getPopupNameFor($link);
                        $linkHandlerName = $gridUniqueName . ucfirst($this->convertLabel2Variable($link));
                        $this->addOpenLinkAction($linkHandlerName, $resourceName);
                        $actionTriggers["on:$action"] = '{(e) => data.'.$resourceName.'.open=true}';
                    }
                }
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
        $node->setAttribute('on:action', '{(e) => Object.assign(data, handler(e.detail.action, e.detail, data))}');
        foreach ($actionTriggers as $event => $handler) {
            $node->setAttribute($event, $handler);
        }
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

    // FileBrowser
    public function Tree($control, $controlProperties, DOMElement $svelteScreen, $configObject)
    {
        error_log(json_encode($control));
        $node = $svelteScreen->ownerDocument->createElement('FileBrowser');
        $name = explode("\n", $controlProperties['text'])[0];
        $name = substr($name, 0, 2) === "F " ? substr($name, 2) : $name;
        $name = $this->convertLabel2Variable($name);
        $svelteScreen->appendChild($node);
        $rows = round($control['h'] / 19);
        $node->setAttribute('rows', '{' . $rows . '}');
        $node->setAttribute('bind:value', '{data.' . $name . '}');

        // add code to script
        $script = $svelteScreen->ownerDocument->getElementsByTagName('script')->item(0);
        $this->addUniqueScriptCode($script, 'import FileBrowser from "$lib/FileBrowser.svelte";');
        $this->addScreenData($name, '""');
    }

    private function addOpenLinkAction($handlerName, $popupName)
    {
        $this->addHandler($handlerName, "openPopup('{$popupName}');");
    }

    private function addHandler($handlerName, $code = '')
    {
        $this->handlers[$handlerName] = [
            'name' => $handlerName,
            'code' => $code,
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
        $name = $this->commentAt($control, $name);
        $storeName = 'store' . ucfirst($name);
        $node->setAttribute('bind:value', '{data.' . $name . '}');
        $configObject->$storeName = [
            'url' => 'users',
        ];
        $node->setAttribute('store', '{new ApiStore(config.' . $storeName . ')}');
        $node->setAttribute('configStore', '{configStore}');
        $placeholder = '{__(' . json_encode($name, JSON_UNESCAPED_UNICODE) . ')}';
        $node->setAttribute('placeholder', $placeholder);

        // add code to script
        $script = $svelteScreen->ownerDocument->getElementsByTagName('script')->item(0);
        $this->addUniqueScriptCode($script, 'import ApiStore  from "$lib/ApiStore";');
        $this->addUniqueScriptCode($script, 'import ComboBox from "$lib/ComboBox.svelte";');
        $this->addUniqueScriptCode($script, 'import ConfigStore from "$lib/ConfigStore";');
        $this->addUniqueScriptCode($script, 'import config from "./config.json";');
        $this->addUniqueScriptCode($script, 'let configStore = new ConfigStore("' . $this->name . '", config);');
        $this->addScreenData($name, '""');
    }

    private function Label($control, $controlProperties, DOMElement $svelteScreen, $configObject)
    {
        // error_log(json_encode($controlProperties));
        $node = $svelteScreen->ownerDocument->createElement('label');
        $node->nodeValue = $controlProperties['text'];
        // styles
        $style = "";
        // color
        if (isset($controlProperties['color'])) {
            // convert to hex with leading zeros
            $color = dechex($controlProperties['color']);
            $color = str_pad($color, 6, '0', STR_PAD_LEFT);
            $style .= "color: #{$color};";
        }
        // bold
        if (isset($controlProperties['bold']) && $controlProperties['bold']) {
            $style .= "font-weight: bold;";
        }
        // italic
        if (isset($controlProperties['italic']) && $controlProperties['italic']) {
            $style .= "font-style: italic;";
        }
        // underline
        if (isset($controlProperties['underline']) && $controlProperties['underline']) {
            $style .= "text-decoration: underline;";
        }
        // align
        if (isset($controlProperties['align'])) {
            $style .= "width: 100%; text-align: {$controlProperties['align']};";
        }
        // size
        if (isset($controlProperties['size'])) {
            $defaultBalsamiqSize = 13;
            $size = round($controlProperties['size'] / $defaultBalsamiqSize * 10) * 0.1;
            $style .= "font-size: {$size}rem;";
        }
        if ($style) {
            $node->setAttribute('style', $style);
        }
        $svelteScreen->appendChild($node);
        // add code to script
        $script = $svelteScreen->ownerDocument->getElementsByTagName('script')->item(0);
        $this->addUniqueScriptCode($script, 'import { translation as __ } from "$lib/translations";');
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
        $popupWidth = min(ceil(($control['w'] / $this->windowWidth) * 10), 10) * 10;
        $node->setAttribute('class', 'popup-' . $popupWidth);
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
        $this->popupControl = $control;
    }

    // <ContentDialog >
    public function Alert($control, $controlProperties, DOMElement $div, $configObject)
    {
        // error_log(json_encode($control));
        $node = $div->ownerDocument->createElement('ContentDialog');
        $label = $controlProperties['text'];
        $name = $this->getPopupNameFor($control['resourceID']);
        $node->setAttribute('bind:open', '{data.' . $name . '.open}');
        // $node->setAttribute('title', '{__(' . json_encode($label, JSON_UNESCAPED_UNICODE) . ')}');
        $node->setAttribute('size', 'max');
        // $popupWidth = min(ceil(($control['w'] / $this->windowWidth) * 10), 10) * 10;
        // $node->setAttribute('class', 'popup-' . $popupWidth);
        $node->appendChild($div->ownerDocument->createTextNode("\n"));
        $screen = $div->parentNode->parentNode;
        $screen->appendChild($node);
        $screen->removeChild($div->parentNode);
        // add code to script
        $script = $div->ownerDocument->getElementsByTagName('script')->item(0);
        $this->addUniqueScriptCode($script, 'import { ContentDialog } from "fluent-svelte";');
        // $this->addUniqueScriptCode($script, 'let ' . $name . ' = {open: false};');
        $this->addScreenData($name, json_encode(['open' => false]));
        // Last line of label contains the buttons
        $lines = explode("\n", $label);
        $buttons = array_pop($lines);
        $label = implode("\n", $lines);
        // Add text to popup with class="alert-text"
        $textNode = $div->ownerDocument->createElement('p');
        $textNode->appendChild($div->ownerDocument->createTextNode('{__(' . json_encode($label, JSON_UNESCAPED_UNICODE) . ', data)}' . "\n"));
        $textNode->setAttribute('class', 'alert-text');
        $node->appendChild($textNode);
        // Add buttons to popup
        $center = $div->ownerDocument->createElement('center');
        $node->appendChild($center);
        $center->appendChild($div->ownerDocument->createTextNode("\n"));
        $node->appendChild($div->ownerDocument->createTextNode("\n"));
        $buttons = explode(',', $buttons);
        $alertUniqueName = "alert{$control['ID']}";
        foreach ($buttons as $i => $button) {
            $button = trim($button);
            $buttonNode = $div->ownerDocument->createElement('Button');
            $buttonNode->appendChild($div->ownerDocument->createTextNode("\n{__(" . json_encode($button, JSON_UNESCAPED_UNICODE) . ", data)}\n"));
            $link = $controlProperties['hrefs']['href'][$i] ?? null;
            if ($link && $link['ID']) {
                $link = $link['ID'];
                $resourceName = $this->getPopupNameFor($link);
                $linkHandlerName = $alertUniqueName . ucfirst($this->convertLabel2Variable($button));
                $this->addOpenLinkAction($linkHandlerName, $resourceName);
                $buttonNode->setAttribute('on:click', '{(e) => { Object.assign(data, handler(' . json_encode($linkHandlerName) . ', e.detail, data)); data.' . $resourceName . '.open=true}}');
                $this->addScreenData($resourceName, json_encode(['open' => false]));
            }
            $center->appendChild($buttonNode);
            $center->appendChild($div->ownerDocument->createTextNode("\n"));
        }
        // move children to dialog
        $this->popup = $node;
        $this->popupControl = $control;
    }

    private function getPopupNameFor($resourceID)
    {
        $name = $this->getResourceName($resourceID);
        $name = 'popup' . ucfirst($this->convertLabel2Variable($name));
        return $name;
    }

    public function convertLabel2Variable($label)
    {
        // remove boundary - - used to identify placeholder
        $label = preg_replace('/^-/', '', $label);
        $label = preg_replace('/-$/', '', $label);
        // remove accents ?? -> ?? -> e ...
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
        // add variable prefix if starts with number
        if (preg_match('/^[0-9]/', $label)) {
            $label = 'var' . $label;
        }
        // camel case
        $label = preg_replace_callback('/_([a-z])/', function ($matches) {
            return strtoupper($matches[1]);
        }, $label);
        return $label;
    }
    public function normalize($string)
    {
        $table = [
            '??'=>'S', '??'=>'s', '??'=>'Dj', '??'=>'dj', '??'=>'Z', '??'=>'z', '??'=>'C', '??'=>'c', '??'=>'C', '??'=>'c',
            '??'=>'A', '??'=>'A', '??'=>'A', '??'=>'A', '??'=>'A', '??'=>'A', '??'=>'A', '??'=>'C', '??'=>'E', '??'=>'E',
            '??'=>'E', '??'=>'E', '??'=>'I', '??'=>'I', '??'=>'I', '??'=>'I', '??'=>'N', '??'=>'O', '??'=>'O', '??'=>'O',
            '??'=>'O', '??'=>'O', '??'=>'O', '??'=>'U', '??'=>'U', '??'=>'U', '??'=>'U', '??'=>'Y', '??'=>'B', '??'=>'Ss',
            '??'=>'a', '??'=>'a', '??'=>'a', '??'=>'a', '??'=>'a', '??'=>'a', '??'=>'a', '??'=>'c', '??'=>'e', '??'=>'e',
            '??'=>'e', '??'=>'e', '??'=>'i', '??'=>'i', '??'=>'i', '??'=>'i', '??'=>'o', '??'=>'n', '??'=>'o', '??'=>'o',
            '??'=>'o', '??'=>'o', '??'=>'o', '??'=>'o', '??'=>'u', '??'=>'u', '??'=>'u', '??'=>'y', '??'=>'y', '??'=>'b',
            '??'=>'y', '??'=>'R', '??'=>'r',
        ];

        return strtr($string, $table);
    }
}
