<?php

require __DIR__ . '/Balsamic.php';

// Verify input $_POST
if (!isset($_POST['data'])) {
    throw new Exception('Missing data');
}

$name = 'test';

$data = $_POST['data'];
$filepath = $_ENV['STORAGE_PATH'] . '/' . $data['attributes']['filename'];
$balsamic = new Balsamic($filepath, $name);
$folder = $_ENV['FRONTEND_PAGES_PATH'] . '/' . $name;
// create folder if not exists
if (!file_exists($folder)) {
    mkdir($folder, 0777, true);
}
// create file
$filename = $folder . '/+page.svelte';
$balsamic->toSvelte($filename);
