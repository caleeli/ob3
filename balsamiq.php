<?php

$sqlite_file ='/home/david/Downloads/muestra_de_subida_de_carpetas.bmpr';

$preview = $argv[1] ?? false;
$withLayout = false;
$windowWidth = 1182;
$layoutLeft = 100;
$layoutTop = 116;

$connection = new PDO('sqlite:' . $sqlite_file);
$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Select all the rows in the RESOURCES table
$statement = $connection->prepare('SELECT * FROM RESOURCES');
$statement->execute();
$resources = $statement->fetchAll(PDO::FETCH_ASSOC);

$svelteScreen = new DOMDocument();
// Add <script lang="ts"> to the document
$script = $svelteScreen->createElement('script');
$script->setAttribute('lang', 'ts');
$svelteScreen->appendChild($script);
$svelteScreen->appendChild($svelteScreen->createTextNode("\n"));

foreach ($resources as $resource) {
    $id = $resource['ID'];
    $branch = $resource['BRANCHID'];
    $attributes = json_decode($resource['ATTRIBUTES']);
    $data = $resource['DATA'];
    if (substr($data, 0, 1) == '{') {
        $data = json_decode($data, true);
    }

    // Print resource
    debug($id . ' - ' . $branch . "\n");
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
        $controlList = detectHorizontalMerges($controlList);
        foreach ($controlList as $control) {
            $controlType = $control['typeID'];
            // debug($controlType . ": " . json_encode($control, JSON_UNESCAPED_UNICODE));
        }
        $slots = normalizeSizePosition($controlList);
        // debug(json_encode($slots, JSON_UNESCAPED_UNICODE));
        slots2WebComponents($slots, $svelteScreen, $script);
    }
}

if ($preview) {
    $html = $svelteScreen->saveXML();
    // decode htmlentities
    $html = html_entity_decode($html);
    echo $html;
}

function debug(...$params)
{
    global $preview;
    if ($preview) {
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
function horizontalMerge($left, $right)
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

function detectHorizontalMerges($controls)
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
                $merged = horizontalMerge($left, $right);
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

function normalizeSizePosition($controls)
{
    global $windowWidth;
    global $layoutLeft;
    global $layoutTop;
    $maxColumns = 2;
    $columnWidth = $windowWidth / $maxColumns;
    $rowHeight = 32;
    $slots = [[]];
    foreach ($controls as $i => $control) {
        $x = $control['x'] - $layoutLeft;
        $y = $control['y'] - $layoutTop;
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

function slots2WebComponents($rows, DomDocument $svelteScreen)
{
    foreach ($rows as $row) {
        $divRow = $svelteScreen->createElement('div');
        // $divRow->setAttribute('class', 'row');
        foreach ($row as $column) {
            $divCol = $svelteScreen->createElement('div');
            // $divCol->setAttribute('class', 'column');
            $divRow->appendChild($divCol);
            foreach ($column as $control) {
                $controlType = $control['typeID'];
                debug($controlType);
                $controlTypeFn = $controlType . 'Component';
                if (function_exists($controlType)) {
                    $controlType($control, $control['properties'], $divCol);
                } elseif (function_exists($controlTypeFn)) {
                    $controlTypeFn($control, $control['properties'], $divCol);
                }
            }
        }
        $svelteScreen->appendChild($divRow);
        // $svelteScreen->appendChild($svelteScreen->createTextNode("\n"));
    }
}


////
function AppBar($controlPosition, $controlProperties, DOMElement $svelteScreen)
{
    global $withLayout;
    if (!$withLayout) {
        return;
    }
    $node = $svelteScreen->ownerDocument->createElement('AppBar');

    $svelteScreen->appendChild($node);
}

////
function Subtitle($controlPosition, $controlProperties, DOMElement $svelteScreen)
{
    $node = $svelteScreen->ownerDocument->createElement('h2');
    $node->nodeValue = '{__(' . json_encode($controlProperties['text'], JSON_UNESCAPED_UNICODE) . ')}';
    $svelteScreen->appendChild($node);
}

////
function ListComponent($controlPosition, $controlProperties, DOMElement $svelteScreen)
{
    // if x < 10 => Menu
    $isMenu = $controlPosition['x'] < 10;
    if ($isMenu) {
        global $withLayout;
        if (!$withLayout) {
            return;
        }
        $node = $svelteScreen->ownerDocument->createElement('Menu');
        $svelteScreen->appendChild($node);
    }
}

////
function FileInput($controlPosition, $controlProperties, DOMElement $svelteScreen)
{
    $node = $svelteScreen->ownerDocument->createElement('FileInput');
    // bind value
    $name = $controlProperties['text'];
    $name = preg_replace('/[^a-zA-Z0-9]+/', '_', $name);
    $label = '{__(' . json_encode($controlProperties['text'], JSON_UNESCAPED_UNICODE) . ')}';
    $node->setAttribute('bind:value', $name);
    $node->setAttribute('placeholder', $label);
    $svelteScreen->appendChild($node);
}

////
function Button($controlPosition, $controlProperties, DOMElement $svelteScreen)
{
    $node = $svelteScreen->ownerDocument->createElement('Button');
    $node->nodeValue = $controlProperties['text'];
    $svelteScreen->appendChild($node);
}

////
function TextInput($controlPosition, $controlProperties, DOMElement $svelteScreen)
{
    $node = $svelteScreen->ownerDocument->createElement('TextInput');
    $node->nodeValue = $controlProperties['text'];
    $svelteScreen->appendChild($node);
}
