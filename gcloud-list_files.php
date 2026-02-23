<?php
// Include the main.php file
include 'main.php';
// Check if the user is logged in, if not then redirect to login page
check_loggedin($con);
// Template code below

require 'vendor/autoload.php';

use Google\Cloud\Storage\StorageClient;

$GoogleCredentials = Google_Application_Creadentials_file;

putenv('GOOGLE_APPLICATION_CREDENTIALS='.$GoogleCredentials);

$Q = explode("/", $_SERVER['QUERY_STRING']);
parse_str($Q[0],$QueryParameters);
$bucketname = $QueryParameters['bucketname'];

function list_objects_with_prefix(string $bucketName, string $directoryPrefix): void
{
    $storage = new StorageClient();
    $bucket = $storage->bucket($bucketName);
    $options = ['prefix' => $directoryPrefix];
    foreach ($bucket->objects($options) as $object) {
        printf('%s <br>' . PHP_EOL, $object->name());   
    }
}

list_objects_with_prefix($bucketname, '1/');