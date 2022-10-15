<?php

global $_PATH;

$model = $_PATH[1];
if (!$model) {
    return [];
}

$class = require "{$model}.php";
$def = new $class;

return [
    'data' => $def,
];
