<?php
// Include the main.php file
include 'main.php';

// Write an entry to the cron log file before we start
$logFile = LOG_FILE_PATH;
$publicKeyPath = public_key_path;

$dateNow = new DateTime('now', new DateTimeZone('Pacific/Auckland'));
$currentDateTime = $dateNow->format('Y-m-d H:i:s');

$logMessage = "Webhook POST on: $currentDateTime";
file_put_contents($logFile, $logMessage, FILE_APPEND);

$headers = getallheaders();
$signature = $headers['X-Akahu-Signature'];
$signingKeyId = $headers['X-Akahu-Signing-Key'];

$body = file_get_contents("php://input");

if (json_last_error() === JSON_ERROR_NONE) {

} else {
    $logMessage = "Invalid JSON!";
	file_put_contents($logFile, $logMessage, FILE_APPEND);
}

//Get the AppToken
$sqlauth = "SELECT * FROM akahu_authorization";
$resultauth = $con->query($sqlauth);
if ($resultauth->num_rows > 0) {
	while($rowauth = $resultauth->fetch_assoc()) {
		$appToken = $rowauth["appToken"];
	}
}
// Get the user access token
$sqlauth1 = "SELECT akahu_access_token FROM accounts WHERE id = $signingKeyId";
$resultauth1 = $con->query($sqlauth1);
if ($resultauth1->num_rows > 0) {
    while($rowauth1 = $resultauth1->fetch_assoc()) {
        $authorization = "Bearer " . $rowauth1["akahu_access_token"];
    }
}

//echo "AppToken: ".$appToken."\n";
//echo "Authorization: ".$authorization."\n";

// Check to see if the cached public key file exists and is less than 24 hours old
function getPublicKey () {
	$cacheDuration = 24 * 60 * 60;
	if (file_exists(public_key_path) && (time() - filemtime(public_key_path)) < $cacheDuration) {
		return file_get_contents(public_key_path);
	} else {
		$con = mysqli_connect(db_host, db_user, db_pass, db_name);
		$headers = getallheaders();
		$signingKeyId = $headers['X-Akahu-Signing-Key'];
		//Get the client_id and secret
		$sqlauth = "SELECT * FROM akahu_authorization";
		$resultauth = $con->query($sqlauth);
		if ($resultauth->num_rows > 0) {
			while($rowauth = $resultauth->fetch_assoc()) {
				$username = $rowauth["appToken"];
				$password = $rowauth["appSecret"];
			}
		}
		// Initialize a cURL session
		$geturl = "https://api.akahu.io/v1/keys/".$signingKeyId;
		$ch = curl_init($geturl);
		curl_setopt($ch,CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			"Authorization: Basic " . base64_encode("$username:$password")
		]);
		// Execute the request
		$response = curl_exec($ch);
		$data = json_decode($response, true); // Decode as an associative array
		curl_close($ch);
		// Save the file
		if (file_put_contents(public_key_path, $data['item']) !==false) {
			return $data['item'];
		} else {
			return "";
		}
	}
}
$public_key = getPublicKey();

// Convert the PKCS#1 key to X.509 format (manually, as PHP doesn't natively support PKCS#1)
passthru("openssl rsa -RSAPublicKey_in -in public_key.pem -pubout -out public_key_compatible.pem");
$public_key_compatible = openssl_pkey_get_public(public_key_compatible_path);

// Verify the signature
$verify = openssl_verify($body, base64_decode($signature), $public_key_compatible, OPENSSL_ALGO_SHA256);

if ($verify === 1) {
    echo " - This webhook is from Akahu! - ";
	$logMessage = " - Webhook is from Akahu! - ";
	file_put_contents($logFile, $logMessage, FILE_APPEND);
} elseif ($verify === 0) {
    echo " - Invalid webhook caller! \n";
	$logMessage = " - Invalid webhook caller! \n";
	file_put_contents($logFile, $logMessage, FILE_APPEND);
} else {
   echo " - Error verifying signature: \n" . openssl_error_string(). "\n";
   $logMessage = " - Error verifying signature: \n" . openssl_error_string(). "\n";
	file_put_contents($logFile, $logMessage, FILE_APPEND);
}

//Extract the webhook type and code from the payload
$bodyParsed = json_decode($body, true);
if (isset($bodyParsed['webhook_type'])) {
	$webhook_type = $bodyParsed['webhook_type'];
	$webhook_code = $bodyParsed['webhook_code'];
	$state = $bodyParsed['state'];
	switch ($webhook_type) {
		case 'ACCOUNT':
			echo "ACCOUNT\n";
			$logMessage = "ACCOUNT\n";
			file_put_contents($logFile, $logMessage, FILE_APPEND);
			if ($webhook_code === 'WEBHOOK_CANCELLED') {

			} else {
				$item_id = $bodyParsed['item_id'];
				switch ($webhook_code) {
					case 'CREATE':

						break;
					case 'UPDATE':

						break;
					case 'DELETE':

						break;
				}
			}
			break;
		case 'PAYMENT':
			echo "PAYMENT\n";
			$logMessage = "PAYMENT";
			file_put_contents($logFile, $logMessage, FILE_APPEND);
			if ($webhook_code === 'WEBHOOK_CANCELLED') {
	
			} else {
				$paymentid = $bodyParsed['item_id'];
				$logMessage = " - ".$paymentid."\n";
				file_put_contents($logFile, $logMessage, FILE_APPEND);
				switch ($webhook_code) {
					case 'UPDATE':
						//Check the status of the Akahu payment
						$geturl = "https://api.akahu.io/v1/payments/".$paymentid;
						$ch = curl_init($geturl);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
						curl_setopt($ch, CURLOPT_HTTPHEADER, [
						"X-Akahu-Id: $appToken",
						"Authorization: $authorization"
						]);

						// Execute the request
						$response1 = curl_exec($ch);
						// Check for cURL errors
						if ($response1 === false) {
							echo "cURL Error: " . curl_error($ch);
							$logMessage = "cURL Error: " . curl_error($ch) . "\n";
							file_put_contents($logFile, $logMessage, FILE_APPEND);
						} else {
							$data1 = json_decode($response1, true); // Decode as an associative array
							if (isset($data1['message'])) {
								if ($data1['message'] == "Unauthorized") {

								}
							}
//$logMessage = $response1."\n";
file_put_contents($logFile, $logMessage, FILE_APPEND);

							$paymentstatus = $data1['item']['status'];
							$final = $data1['item']['final'];

$logMessage = $paymentstatus."\n";
file_put_contents($logFile, $logMessage, FILE_APPEND);	

							//Update the invoice status
							if ($final == "true") {
switch ($paymentstatus) {
	case 'CANCELLED':
	case 'ERROR':
	case 'DECLINED':
								$status_code = $data1['item']['status_code'];
								$status_text = $data1['item']['status_text'];
								$logMessage = $status_code.": ".$status_text."\n";
								file_put_contents($logFile, $logMessage, FILE_APPEND);
								// Set the invoice status to ERROR and save the error code into akahuPaymentID field
								switch ($paymentstatus) {
									case 'CANCELLED':
										$statusid = 10;
										break;
									case 'ERROR':
										$statusid = 7;
										break;
									case 'DECLINED':
										$statusid = 8;
										break;
								}
								$stmt = $con->prepare('UPDATE transactions SET invoiceStatusID = ? WHERE akahuPaymentID = ?');
								$stmt->bind_param('is', $statusid, $paymentid);
								$stmt->execute();
								$stmt->close();
								// Get the updated invoice
								$stmt = $con->prepare("SELECT * from transactions WHERE akahuPaymentID = ?");
								$stmt->bind_param("s", $paymentid);
								$stmt->execute();
								$result_invoice = $stmt->get_result();
								$stmt->close();
								if ($result_invoice->num_rows > 0) {
									while($rowinvoice = $result_invoice->fetch_assoc()) {
										$opexinvoiceid = $rowinvoice['idtransaction'];
										// Look for an existing record in paymentstatus table
										$stmt1 = $con->prepare("SELECT * from paymentstatus WHERE opexInvoiceID = ?");
										$stmt1->bind_param("i", $opexinvoiceid);
										$stmt1->execute();
										$result_payment = $stmt1->get_result();
										$stmt1->close();
										if ($result_payment->num_rows > 0) {
//$logMessage = "Updating paymentstatus: " . $opexinvoiceid . "\n";
//file_put_contents($logFile, $logMessage, FILE_APPEND);
											// Add a record to the paymentstatus table
											$stmt = $con->prepare("UPDATE paymentstatus SET akahuPaymentID = ?, statusCode = ?, statusText = ? WHERE opexInvoiceID = ?");
											$stmt->bind_param("sssi", $paymentid, $status_code, $status_text, $opexinvoiceid);
											$stmt->execute();
											$stmt->close();
										} else {
//$logMessage = "Adding paymentstatus: " . $paymentid . "\n";
//file_put_contents($logFile, $logMessage, FILE_APPEND);
											// Add a record to the paymentstatus table
											$stmt = $con->prepare("INSERT INTO paymentstatus (opexInvoiceID, akahuPaymentID, statusCode, statusText) VALUES (?,?,?,?)");
											$stmt->bind_param("isss", $opexinvoiceid, $paymentid, $status_code, $status_text);
											$stmt->execute();
											$stmt->close();
										}
									}
								}
								
								break;
	case 'SENT':
								$statusid = 4;
								$stmt = $con->prepare('UPDATE transactions SET invoiceStatusID = ? WHERE akahuPaymentID = ?');
								$stmt->bind_param('is', $statusid, $paymentid);
								$stmt->execute();
								$stmt->close();

								$logMessage = "payment confirmed. \n";
								file_put_contents($logFile, $logMessage, FILE_APPEND);
								break;
}
								
							} else {
								switch ($paymentstatus) {
									case 'PENDING_APPROVAL':
										$statusid = 9;
										$stmt = $con->prepare('UPDATE transactions SET invoiceStatusID = ? WHERE akahuPaymentID = ?');
										$stmt->bind_param('is', $statusid, $paymentid);
										$stmt->execute();
										$stmt->close();
										break;
								}
								$logMessage = "payment NOT confirmed. \n";
								file_put_contents($logFile, $logMessage, FILE_APPEND);
							}
						}
						break;
					case 'RECEIVED':
						
					break;
				}
			}
			break;
		case 'TRANSACTION':
			echo "TRANSACTION\n";
			$logMessage = "TRANSACTION\n";
			file_put_contents($logFile, $logMessage, FILE_APPEND);
			if ($webhook_code === 'WEBHOOK_CANCELLED') {

			} else {
				$item_id = $bodyParsed['item_id'];
				switch ($webhook_code) {
					case 'INITIAL_UPDATE':

						break;
					case 'DEFAULT_UPDATE':

						break;
					case 'DELETE':

						break;
				}
			}
			break;
		case 'TRANSFER':
			echo "TRANSFER\n";
			$logMessage = "TRANSFER\n";
			file_put_contents($logFile, $logMessage, FILE_APPEND);
			if ($webhook_code === 'WEBHOOK_CANCELLED') {
	
			} else {
				$item_id = $bodyParsed['item_id'];
				switch ($webhook_code) {
					case 'UPDATE':
	
						break;
					case 'RECEIVED':
	
						break;
				}
			}
			break;
		case 'USER':
			echo "USER\n";
			$logMessage = "USER\n";
			file_put_contents($logFile, $logMessage, FILE_APPEND);
			break;
		case 'TOKEN':
			echo "TOKEN\n";
			$logMessage = "TOKEN\n";
			file_put_contents($logFile, $logMessage, FILE_APPEND);
				if ($webhook_code === 'DELETE') {
					$item_id = $bodyParsed['item_id'];
				}
			break;
		default:
			
	}
}

$logMessage = "End.\n";
file_put_contents($logFile, $logMessage, FILE_APPEND);

//echo "Akahu webhook page";?>