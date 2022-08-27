<?php

global $_PATH;

$id = $_PATH[1];
$configFile = __DIR__ . '/../src/routes/' . $_PATH[1] . '.json';
$config = json_encode($_POST['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
file_put_contents($configFile, $config);

return [
    'data' => $_POST['data'],
    'path' => $configFile,
];
