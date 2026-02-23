<?php
require 'vendor/autoload.php';
require_once('xero-php-oauth2-app/storage.php');

// Storage Classe uses sessions for storing token > extend to your DB of choice
$storage = new StorageClass();

// Initialize Identity API
$config = XeroAPI\XeroPHP\Configuration::getDefaultConfiguration()->setAccessToken( (string)$storage->getSession()['token'] );
$identityApi = new XeroAPI\XeroPHP\Api\IdentityApi(
    new GuzzleHttp\Client(),
    $config // Your standard configuration with the access token
);

// Get all connections
$connections = $identityApi->getConnections();

foreach ($connections as $connection) {
    echo "Name: " . $connection->getTenantName() . "<br>";
    echo "Tenant ID: " . $connection->getTenantId() . "<br>";
}
?>
