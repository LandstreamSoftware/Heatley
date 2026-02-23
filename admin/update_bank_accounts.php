<?php
// Access this page using an authorization token.
$cron_token = '2106228793588370e86358956424a14339d1812bc50e301050da049eebe5dd';

// Check if the request contains the correct token 
if (php_sapi_name() !== 'cli' && (!isset($_GET['token']) || $_GET['token'] !== $cron_token)) { 
    http_response_code(403); // Forbidden 
    die('Access denied.'); 
} else {
    // Token matches.

    include 'main.php';

    // Write an entry to the cron log file before we start
    $logFile = LOG_FILE_PATH;
    $dateNow = new DateTime('now', new DateTimeZone('Pacific/Auckland'));
    $currentDateTime = $dateNow->format('Y-m-d H:i:s');

    $logMessage = "Updating bank accounts: $currentDateTime - \n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);

    //Get the api connection urls and tokens
    $sql = "SELECT * FROM connections";
    $result = $con->query($sql);

    if ($result->num_rows > 0) {
        // output data of each connection
        while($row = $result->fetch_assoc()) {
            $apiUrl = $row["apiUrl"];
            $appToken = $row["appToken"];
            $userToken = $row["userToken"];
            $connectionname = $row["connectionName"];

echo "Connection:" . $connectionname . "<br>";

            // Initialize cURL session
            $ch = curl_init($apiUrl."/accounts");

            // Set cURL options
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "X-Akahu-Id: $appToken",
            "Authorization: $userToken"
            ]);
            // Execute the request
            $response = curl_exec($ch);
            // Check for cURL errors
            if ($response === false) {
                echo "cURL Error: " . curl_error($ch);
                //$logMessage = "cURL Error: " . curl_error($ch) . "\n";
            } else {
                //echo "Response: " . $response;
                //$logMessage = "Response: " . $response . "\n";
                $data = json_decode($response, true); // Decode as an associative array
                if ($data === null) {
                    die("Error decoding JSON response.");
                }
                $items = $data['items'];

echo count($items) . " accounts.<br>";

            }
            // Close the cURL session
            curl_close($ch);

            $importCount = 0;
            $accountCount = 0;

            //For each account
            foreach ($items as $account) {
                $_id = $account['_id'];
                $connection_name = $account['connection']['name'];
                $connection_logo = $account['connection']['logo'];
                $name = $account['name'];
                $formatted_account = $account['formatted_account'];
                $status = $account['status'];
                $type = $account['type'];
                $holder = $account['meta']['holder'];
                $recordownerid = 0;

echo "&nbsp;&nbsp;&nbsp;" . $name;

                $sql1 = "SELECT * FROM bankaccounts where _id = '$_id'";
                $result1 = $con->query($sql1);

                if ($result1->num_rows == 0) {
                    //Add the account
                    $stmt = $con->prepare("INSERT INTO bankaccounts (_id, connection_name, connection_logo, name, formatted_account, status, type, holder, recordOwnerID) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("ssssssssi", $_id, $connection_name, $connection_logo, $name, $formatted_account, $status, $type, $holder, $recordownerid);

                    if ($stmt->execute()) {
                        echo "Account " . $holder . " added<br>";
                        $importCount += 1;
                    } else {
                            echo "failed";
                    }
                } else {
                    echo " - account already loaded.<br>";
                }
                
                $accountCount += 1;
            }
        }


    }

    echo $importCount . " bank accounts added from " . $accountCount . "<br>";
}
