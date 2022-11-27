<?php

/**
 * Google OAuth 2.0 auth url 
 */

use Google\Client;
use Google\Service\Drive;

$base = dirname($_SERVER['SCRIPT_URI']);

$configJsonPath = realpath(getenv('GOOGLE_APPLICATION_CREDENTIALS'));
$client = new Client();
$client->setAuthConfig($configJsonPath);
$client->useApplicationDefaultCredentials();
$client->setRedirectUri($base . '/gdrive_cb');
$client->addScope(Drive::DRIVE);
// with refresh_token
$client->setAccessType('offline');

$auth_url = $client->createAuthUrl();
header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
