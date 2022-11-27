<?php

/**
 * Google OAuth 2.0 callback
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

$response = $client->fetchAccessTokenWithAuthCode($_GET['code']);
$token = $client->getAccessToken();
if (!$token) {
    http_response_code(401);
    print_r($response);
    exit;
}
$token = json_encode($token);
$frontendUrl = $_ENV['FRONTEND_URL'];
?>
<script>
    window.opener.postMessage(<?php echo json_encode($token); ?>, <?php echo json_encode($frontendUrl); ?>);
    window.close();
</script>