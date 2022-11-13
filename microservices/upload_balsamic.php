<?php

$upload = upload([
    'field' => 'file',
    'allowed' => ['bmpr']
]);

return [
    'data' => [
        'attributes' => [
            'url' => $upload['url'],
            'filename' => $upload['path'],
            'upload_name' => $upload['filename'],
        ],
    ],
];
