<?php
// Access this page using an authorization token.
$cron_token = '9d1812bc50e301050da049eebe5dd2106228793588370e86358956424a1433a8';

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

    $logMessage = "Getting bank transactions: $currentDateTime - \n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
  

    //Get the api connection url and appToken
    $sql1 = "SELECT * FROM connections WHERE connectionName = 'Akahu_Dev'";
    $result1 = $con->query($sql1);

    if ($result1->num_rows > 0) {
        while($row1 = $result1->fetch_assoc()) {
            $apiUrl = $row1["apiUrl"];
            $appToken = $row1["appToken"];
            $connectionname = $row1["connectionName"];
        }
    }

    //Get each of the User Access Tokens
    $sql = "SELECT akahu_access_token, username FROM accounts WHERE akahu_access_token IS NOT NULL and username <> 'admin'";
    $result = $con->query($sql);

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $userToken = "Bearer " . $row["akahu_access_token"];
            $userName = $row["username"];
    
            //Set the date range for transactions
            $dateminus6 = new DateTime('126 days ago noon', new DateTimeZone('UTC'));
            $startdate = $dateminus6->format('Y-m-d')."T".$dateminus6->format('H:i:s').".000Z";

            $logMessage = "GET bank transactions for $userName using $connectionname: - \n";
            file_put_contents($logFile, $logMessage, FILE_APPEND);

            // Initialize cURL session
            $ch = curl_init($apiUrl."/accounts");

            // Set cURL options
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "X-Akahu-Id: $appToken",
            "Authorization: $userToken"
            ]);
//echo "X-Akahu-Id: " . $appToken . "<br>";
//echo "Authorization: " . $userToken . "<br>";
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
            }
            // Close the cURL session
            curl_close($ch);

            $transactionCount = 0;
            $importCount = 0;

            //For each account get the transactions
            foreach ($items as $account) {
                $bankaccountid = $account['_id'];
                //Get the bank account recordOwnerID
                $sqlbank = "SELECT * FROM bankaccounts WHERE _id = '$bankaccountid'";
                $resultbank = $con->query($sqlbank);

                if ($resultbank->num_rows > 0) {
                    while($rowbank = $resultbank->fetch_assoc()) {
                        $recordownerid = $rowbank["recordOwnerID"];
                    }
                }
                $Url = $apiUrl."/accounts"."/".$account['_id']."/transactions"."?start=".$startdate;
                $ch = curl_init($Url);
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
                } else {
                // No errors
                    $data = json_decode($response, true); // Decode as an associative array
//var_dump($data);
                    if ($data === null) {
                        die("Error decoding JSON response.");
                    }
                    $items = $data['items'];
                    $transactionCount += isset($data['items']) ? count($data['items']) : 0;
                }
                foreach ($items as $transaction) {
                    $transactionid = $transaction['_id'];
                    $transactionaccount = $transaction['_account'];

                    $isoCreatedAt = $transaction['created_at'];
                    $dateTimeCreatedAt = new DateTime($isoCreatedAt);
                    $transactioncreatedat = $dateTimeCreatedAt->format('Y-m-d');

                    $isoUpdatedAt = $transaction['updated_at'];
                    $dateTimeUpdatedAt = new DateTime($isoUpdatedAt);
                    $transactionupdatedat = $dateTimeUpdatedAt->format('Y-m-d');

                    $utcDate = $transaction['date'];
                    $dateTimeDate = new DateTime($utcDate, new DateTimeZone('UTC'));
                    $dateTimeDate->setTimezone(new DateTimeZone('Pacific/Auckland'));
                    $transactiondate = $dateTimeDate->format('Y-m-d');

                    $transactiondescription = $transaction['description'];
                    $transactionamount = $transaction['amount'];
                    $transactionbalance = $transaction['balance'];
                    $transactiontype = $transaction['type'];
                    if (isset($transaction['merchant'])) {
                        $transactionmerchantid = $transaction['merchant']['_id'];
                        $transactionmerchantname = $transaction['merchant']['name'];
                    } else {
                        $transactionmerchantid = '';
                        $transactionmerchantname = '';
                    }
                    if (isset($transaction['meta'])) {
                        if(isset($transaction['meta']['particulars'])) {
//echo "meta particulars: " . $transaction['meta']['particulars'] . "<br>";
                            $transactionparticulars = $transaction['meta']['particulars'];
                        } else {
                            $transactionparticulars = '';
                        }
                        if(isset($transaction['meta']['code'])) {
                            $transactioncode = $transaction['meta']['code'];
                        } else {
                            $transactioncode = '';
                        }
                        if(isset($transaction['meta']['reference'])) {
                            $transactionreference = $transaction['meta']['reference'];
                        } else {
                            $transactionreference = '';
                        }
                    } else {
                        $transactionparticulars = '';
                        $transactioncode = '';
                        $transactionreference = '';
                    }

                    //Look for this transaction in the banktransactions table
                    $sql1 = "SELECT * FROM banktransactions WHERE _id = '$transactionid' and _account = '$transactionaccount' and recordOwnerID = $recordownerid";
                    $result1 = $con->query($sql1);

                    if ($result1->num_rows == 0) {
                        //Insert transaction record
                        $stmt = $con->prepare("INSERT INTO banktransactions (_id, _account, created_at, updated_at, date, description, amount, balance, type, merchant_id, merchant_name, particulars, code, reference, recordOwnerID) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->bind_param("ssssssddssssssi", $transactionid, $transactionaccount, $transactioncreatedat, $transactionupdatedat, $transactiondate, $transactiondescription, $transactionamount, $transactionbalance, $transactiontype, $transactionmerchantid, $transactionmerchantname, $transactionparticulars, $transactioncode, $transactionreference,$recordownerid);

                        if ($stmt->execute()) {
//echo "transaction " . $transactionid . " added<br>";
                            $importCount += 1;
                        } else {
                            echo "transaction " . $transactionid . "failed";
                        }
                    } else {
//echo "transaction " . $transactionid . " already exists<br>";
                    }
                }
                $logMessage = "Account ID: " . $bankaccountid . ": " . $importCount . " of " . $transactionCount . " transactions imported. \n";
                file_put_contents($logFile, $logMessage, FILE_APPEND);
                $importCount = 0;
                $transactionCount = 0;
            }
        echo "Transaction import completed";

        // Close the cURL session
        curl_close($ch);

            
        }   
    }
}