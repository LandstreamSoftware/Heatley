<?php
// This file is run by the Windows Task Scheduler on Dev

// Access this page using an authorization token.
$cron_token = '370e86358956424a1433a9d1812bc5e5dd210622879358880e301050da049eeb';

// Check if the request contains the correct token 
if (php_sapi_name() !== 'cli' && (!isset($_GET['token']) || $_GET['token'] !== $cron_token)) { 
    http_response_code(403); // Forbidden 
    die('Access denied.'); 
} else {
    // Token matches, run these files:

    // Write an entry to the cron log file before we start
    $logFile = LOG_FILE_PATH;
    $dateNow = new DateTime('now', new DateTimeZone('Pacific/Auckland'));
    $currentDateTime = $dateNow->format('Y-m-d H:i:s');

    $logMessage = "Sending renewal reminders: $currentDateTime - \n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);

    $result = include 'sendemail1.php';

    // Write an entry to the cron log file when completed successfully
    $logMessage = "Done - \n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}