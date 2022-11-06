<?php

$upload = upload([
    'field' => 'file',
    'allowed' => ['jpg', 'jpeg', 'png', 'webp'],
    'convert' => [
        'to' => 'jpg',
        'quality' => 75,
        'max_width' => 640,
        'max_height' => 480,
    ],
    'max_size' => 10 * 1024 * 1024,
]);

return [
    'data' => [
        'attributes' => [
            'url' => $upload['url'],
            'filename' => $upload['filename'],
        ],
    ],
];
