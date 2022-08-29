<?php

global $_PATH;

$model = $_PATH[1];
$class = require "{$model}.php";
$def = new $class;

return [
    'data' => $def,
];
