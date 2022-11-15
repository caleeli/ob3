<?php

require __DIR__ . '/Balsamic.php';

$upload = upload([
    'field' => 'file',
    'allowed' => ['bmpr']
]);

$filepath = $_ENV['STORAGE_PATH'] . '/' . $upload['path'];
$balsamic = new Balsamic($filepath, $upload['filename']);
$wireframes = $balsamic->getWireframesList();

return [
    'data' => [
        'attributes' => [
            'url' => $upload['url'],
            'filename' => $upload['path'],
            'upload_name' => $upload['filename'],
            'wireframes' => $wireframes,
        ],
    ],
];
