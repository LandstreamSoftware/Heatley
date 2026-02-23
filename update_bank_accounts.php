<?php
// Include the main.php file
include 'main.php';

$accountid = $_SESSION['account_id'];

$sqlAccess = "SELECT * FROM accesscontrol WHERE accountID = $accountid";
$resultAccess = $con->query($sqlAccess);

$accessto = -1;

if ($resultAccess->num_rows > 0) {
    while($rowAccess = $resultAccess->fetch_assoc()) {
       $accessto .= "," . $rowAccess["companyID"]; 
    }
}

$sql9 = "SELECT * FROM accounts WHERE id = $accountid";
$result9 = $con->query($sql9);

while($row9 = $result9->fetch_assoc()) {
    $recordownerid = $row9["companyID"];
}
?>

<?=template_header('Refresh Accounts')?>

<div class="page-title">
	<div class="icon">
		<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z"/></svg>
	</div>	
	<div class="wrap">
		<h2>Refresh Accounts</h2>
	</div>
</div>

<div class="block">    

<?php
    // Write an entry to the cron log file before we start
    $logFile = LOG_FILE_PATH;
    $dateNow = new DateTime('now', new DateTimeZone('Pacific/Auckland'));
    $currentDateTime = $dateNow->format('Y-m-d H:i:s');

    $logMessage = "Updating bank accounts: $currentDateTime - \n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);

//PART 1
    //Get the api connection urls and tokens
    $sql = "SELECT * FROM akahu_authorization";
    $result = $con->query($sql);
    $sqlauth = "SELECT akahu_access_token FROM accounts WHERE id = $accountid";
    $resultauth = $con->query($sqlauth);
    if ($resultauth->num_rows > 0) {
        while($rowauth = $resultauth->fetch_assoc()) {
            $authorization = "Bearer " . $rowauth["akahu_access_token"];
        }
    }
    if ($result->num_rows > 0) {
        // output data of each connection
        while($row = $result->fetch_assoc()) {
            $appToken = $row["appToken"];
            // Initialize cURL session
            $ch = curl_init("https://api.akahu.io/v1/accounts");
            // Set cURL options
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "accept: application/json",
            "X-Akahu-Id: $appToken",
            "Authorization: $authorization"
            ]);
            // Execute the request
            $response = curl_exec($ch);
//echo "Get Accounts Response: <br>" . $response . "<br><br>";
            // Check for cURL errors
            if ($response === false) {
                echo "cURL Error: " . curl_error($ch);
                //$logMessage = "cURL Error: " . curl_error($ch) . "\n";
            } else {
                $data = json_decode($response, true); // Decode as an associative array
                if (isset($data['message'])) {
                    if ($data['message'] == "Unauthorized") { //There are no accounts in the list??
                        //Remove all bank accounts
                        //Select the bank accounts in the databse
                        $sql3 = "SELECT * FROM bankaccounts";
                        $result3 = $con->query($sql3);
                        if ($result3->num_rows > 0) {
                            // for each bank account
                            while($row3 = $result3->fetch_assoc()) {
                                $_id = $row3["_id"];
//echo "Checking for account ID: " . $_id . "<br><br>";
                                $bankaccountname = $row3["name"];
                                $ch3 = curl_init("https://api.akahu.io/v1/accounts/".$_id);
                                // Set cURL options
                                curl_setopt($ch3, CURLOPT_RETURNTRANSFER, true);
                                curl_setopt($ch3, CURLOPT_HTTPHEADER, [
                                "accept: application/json",
                                "X-Akahu-Id: $appToken",
                                "Authorization: $authorization"
                                ]);
                                // Execute the request
                                $response3 = curl_exec($ch3);
//echo "Get Single Account Response: " . $response3;
                                // Check for cURL errors
                                if ($response3 === false) {
                                    echo "cURL Error: " . curl_error($ch3);
                                } else {
                                    $data3 = json_decode($response3, true); // Decode as an associative array
                                    if ($data3 === null) {
                                        die("Error decoding JSON response.");
                                    }
                                    if (isset($data3['message'])) {
                                        if ($data3['message'] == "Unauthorized") { //Account not found

                                //        }
                                //    }
                                //    $success3 = $data3['success'];
                                //    if ($success3 == '') {
                                        //Remove the bank account
                                            $stmt3 = $con->prepare("DELETE FROM bankaccounts WHERE _id = ?");
                                            $stmt3->bind_param('s', $_id);

                                            if ($stmt3->execute()) {
echo "The bank account <b>" . $bankaccountname . "</b> has been removed.<br>";
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        //Remove the User Access Token from account
                        die("All accounts have been disconnected.");
                    }
                }
                if ($data === null) {
                    die("Error decoding JSON response.");
                }
                $items = $data['items'];
                echo "Number of accounts: " . count($items) . "<br><br>";
                // Close the cURL session
                curl_close($ch);
                $importCount = 0;
                $accountCount = 0;

                //For each account in Akahu, add to database
                foreach ($items as $account1) {
                    //Get the account details
                    $_id = $account1['_id'];
                    $ch1 = curl_init("https://api.akahu.io/v1/accounts/".$_id);
                    // Set cURL options
                    curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch1, CURLOPT_HTTPHEADER, [
                    "accept: application/json",
                    "X-Akahu-Id: $appToken",
                    "Authorization: $authorization"
                    ]);
                    // Execute the request
                    $response1 = curl_exec($ch1);
                    // Check for cURL errors
                    if ($response1 === false) {
                        echo "cURL Error: " . curl_error($ch1);
                    } else {
//echo "Get Account Details Response: " . $response1 . "<br>";
                        $data1 = json_decode($response1, true); // Decode as an associative array
                        if ($data1 === null) {
                            die("Error decoding JSON response.");
                        }
                        $_id = $data1['item']['_id'];
                        $connection_name = $data1['item']['connection']['name'];
                        $connection_logo = $data1['item']['connection']['logo'];
                        $name = $data1['item']['name'];
                        if (isset($data1['item']['formatted_account'])) {
                            $formatted_account = $data1['item']['formatted_account'];
                        } else {
                            $formatted_account = "";
                        }
                        
                        $status = $data1['item']['status'];
                        $type = $data1['item']['type'];
                        //if (isset($data1['item']['meta']['holder'])) {
                            $holder = $data1['item']['meta']['holder'];
                        //} else {
                        //    $holder = "";
                        //}
                    }
//echo "name: " . $connection_name . "<br>";
//echo "logo: " . $connection_logo . "<br>"; 
//echo "holder: " . $holder . "<br>";             
//echo "&nbsp;&nbsp;&nbsp;" . $name;
                    $sql1 = "SELECT * FROM bankaccounts where _id = '$_id'";
                    $result1 = $con->query($sql1);
                    if ($result1->num_rows == 0) {
                        //Add the account
                        $stmt = $con->prepare("INSERT INTO bankaccounts (_id, connection_name, connection_logo, name, formatted_account, status, type, holder, recordOwnerID) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->bind_param("ssssssssi", $_id, $connection_name, $connection_logo, $name, $formatted_account, $status, $type, $holder, $recordownerid);
                        if ($stmt->execute()) {
                            echo "Account <b>" . $name . "</b> added<br>";
                            $importCount += 1;
                        } else {
                            echo "failed";
                        }
                    } else {
                        $sql4 = "UPDATE bankaccounts SET connection_name = ?, connection_logo = ?, name = ?, formatted_account = ?, status = ?, type = ?, holder = ? WHERE _id = ?";
                        $stmt4 = $con->prepare($sql4);
                        $stmt4->bind_param("ssssssss",$connection_name, $connection_logo, $name, $formatted_account, $status, $type, $holder, $_id);
                        if ($stmt4->execute()) {
                            echo "Bank account <b>" . $name . "</b> updated.<br>";
                        } else {
                            echo "Error updating record: " . $con->error;
                        }
                    }
                    $accountCount += 1;
                }
        }
    }
}
    echo "<br>" . $importCount . " bank accounts added from total of " . $accountCount . "<br>";




//PART 2
//Remove any de-authorised accounts.

echo "<br>Checking for disconnected bank accounts...<br><br>";
//Select tha bank accounts in the databse
$sql2 = "SELECT * FROM bankaccounts";
$result2 = $con->query($sql2);
if ($result2->num_rows > 0) {
    // for each bank account
    while($row2 = $result2->fetch_assoc()) {
        $_id = $row2["_id"];
        $bankaccountname = $row2["name"];

        $ch2 = curl_init("https://api.akahu.io/v1/accounts/".$_id);
            // Set cURL options
            curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch2, CURLOPT_HTTPHEADER, [
            "accept: application/json",
            "X-Akahu-Id: $appToken",
            "Authorization: $authorization"
            ]);
            // Execute the request
            $response2 = curl_exec($ch2);

            // Check for cURL errors
            if ($response2 === false) {
                echo "cURL Error: " . curl_error($ch2);
            } else {
                $data2 = json_decode($response2, true); // Decode as an associative array
                if ($data2 === null) {
                    die("Error decoding JSON response.");
                }
                $success = $data2['success'];
                if ($success == '') {
                    //Remove the bank account
                    $stmt1 = $con->prepare("DELETE FROM bankaccounts WHERE _id = ?");
                    $stmt1->bind_param('s', $_id);

                    if ($stmt1->execute()) {
                        echo "The bank account <b>" . $bankaccountname . "</b> has been removed.<br>";
                    }
                }
            }
    }
    echo "All done!<br>";
}
$con->close();
?>
<div class="row">
    &nbsp;
</div>
<div class="row">
    <div class="col-sm-2"><a href="listbankaccounts.php" class="btn btn-primary">Back to Bank Accounts</a></div>
</div>