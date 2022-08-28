<?php

global $_PATH;

$model = $_PATH[1];
require "{$model}.php";
$def = new $model;

return [
    'data' => $def,
];
