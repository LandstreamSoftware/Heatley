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

function list_buckets(): void
{
    $storage = new StorageClient();
    foreach ($storage->buckets() as $bucket) {
        printf('%s <br>' . PHP_EOL, '<a href=gcloud-list_folders.php?bucketname=' . $bucket->name() . '>' . $bucket->name() . '</a>');  
    }
}

list_buckets();