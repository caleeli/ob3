<?php

require __DIR__ . '/Balsamic.php';

$fileID = $_POST['data']['attributes']['fileID'];
$token = $_POST['data']['attributes']['token'];
$content = downloadFile($fileID, $token);

$filepath = $_ENV['STORAGE_PATH'] . '/' . $fileID . '.bmpr';
file_put_contents($filepath, $content);
$filename = $fileID . '.bmpr';
$balsamic = new Balsamic($filepath, $filename);
$wireframes = $balsamic->getWireframesList();

return [
    'data' => [
        'attributes' => [
            'filename' => $filename,
            'upload_name' => $filename,
            'wireframes' => $wireframes,
        ],
    ],
];
