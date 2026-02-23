<?php
// Include the main.php file
include 'main.php';

$accountid = $_SESSION['account_id'];

$sqlAccess = "SELECT * FROM accesscontrol WHERE accountID = $accountid";
$resultAccess = $con->query($sqlAccess);

$accessto = -1;

if ($resultAccess->num_rows > 0) {
	while ($rowAccess = $resultAccess->fetch_assoc()) {
		$accessto .= "," . $rowAccess["companyID"];
	}
}

//Set $code to empty string
$code = "";

$queryString = $_SERVER['QUERY_STRING'];
parse_str($queryString, $parsedQuery);
if (isset($parsedQuery['invoiceid'])) {
	$QPinvoiceid = $parsedQuery['invoiceid'];
}

$sql = "SELECT * from transactions_view WHERE idtransaction = $QPinvoiceid";
$result = $con->query($sql);
if ($result->num_rows == 1) {
	while ($row = $result->fetch_assoc()) {
		$invoicenumber = $row["invoicenumber"];
		$invoicedate = $row["transactiondate"];
		$invoicetotal = $row["transactiontotal"];
		$invoicecompanyid = $row["transactioncompanyid"];
		$invoiceduedate = $row["invoiceduedate"];
		$invoicestatusid = $row["invoicestatusid"];
		$status_code = $row["invoicestatus"];
		$status_text = $row["statustext"];
	}
}
$sql1 = "SELECT * FROM companies where idcompany = $invoicecompanyid";
$result1 = $con->query($sql1);
if ($result1->num_rows == 1) {
	while ($row1 = $result1->fetch_assoc()) {
		$companyname = $row1["companyName"];
		$bankaccountnumber = $row1["bankAccountNumber"];
		if (empty($row1["bankAccountNumber"])) {
			$bankaccountnumberErr = "There is no bank account number loaded for this supplier";
		} else {
			$bankaccountnumberErr = "";
		}
	}
}
$sql2 = "SELECT * FROM bankaccounts where recordOwnerID IN ($accessto)";
$result2 = $con->query($sql2);
$sql3 = "SELECT * FROM companies where companyTypeID = 6 AND recordOwnerID IN ($accessto) ORDER BY companyName";
$result3 = $con->query($sql3);

$theirparticulars = $theircode = $theirreference = $yourcode = $yourreference = "";
$bankaccountErr = $payeebankaccountErr = $invoicetotalErr = $theirparticularsErr = $theircodeErr = $theirreferenceErr = $yourcodeErr = $yourreferenceErr = "";


if ($_SERVER["REQUEST_METHOD"] == "POST") {

	if ($_POST["_id"] == 0) {
		$bankaccountErr = "Bank account is required";
	}

	if (empty($_POST["invoicetotal"])) {
		$invoicetotalErr = "Invoice Amount is required";
	} else {
		$invoicetotal = test_input($_POST["invoicetotal"]);
		//check if the field only contains letters dash or white space
		if (!preg_match("/^[0-9.' ]*$/", $invoicetotal)) {
			$invoicetotalErr = "Only numbers and dot allowed";
		}
	}

	if (empty($_POST["theirparticulars"]) and empty($_POST["theircode"]) and empty($_POST["theirreference"])) {
		$theirparticularsErr = "At lease one value is required";
	}

	if (!empty($_POST["theirparticulars"])) {
		$theirparticulars = test_input($_POST["theirparticulars"]);
		if (!preg_match("/^[a-zA-Z-0-9āēīōūĀĒĪŌŪ' .\/]*$/", $theirparticulars)) {
			$theirparticularsErr = "Disallowed characters used";
		}
	}
	if (!empty($_POST["theircode"])) {
		$theircode = test_input($_POST["theircode"]);
		if (!preg_match("/^[a-zA-Z-0-9āēīōūĀĒĪŌŪ' .\/]*$/", $theircode)) {
			$theircodeErr = "Disallowed characters used";
		}
	}
	if (!empty($_POST["theirreference"])) {
		$theirreference = test_input($_POST["theirreference"]);
		if (!preg_match("/^[a-zA-Z-0-9āēīōūĀĒĪŌŪ' .\/]*$/", $theirreference)) {
			$theirreferenceErr = "Disallowed characters used";
		}
	}
	if (!empty($_POST["yourcode"])) {
		$yourcode = test_input($_POST["yourcode"]);
		if (!preg_match("/^[a-zA-Z-0-9āēīōūĀĒĪŌŪ' .\/]*$/", $yourcode)) {
			$yourcodeErr = "Disallowed characters used";
		}
	}
	if (!empty($_POST["yourreference"])) {
		$yourreference = test_input($_POST["yourreference"]);
		if (!preg_match("/^[a-zA-Z-0-9āēīōūĀĒĪŌŪ' .\/]*$/", $yourreference)) {
			$yourreferenceErr = "Disallowed characters used";
		}
	}
	if (!empty($_POST["_id"])) {
		$_id = $_POST["_id"];
	}
}
function test_input($data)
{
	$data = trim($data);
	$data = stripslashes($data);
	$data = htmlspecialchars($data);
	return $data;
}



?>

<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,minimum-scale=1">
	<title>Pay OPEX Bill</title>
	<link href="style.css" rel="stylesheet" type="text/css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" rel="stylesheet">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
	<style>
		.pagination a {
			color: black;
			float: left;
			padding: 8px 16px;
			text-decoration: none;
			transition: background-color .3s;
		}

		.pagination a.active {
			background-color: dodgerblue;
			color: white;
		}

		.pagination a:hover:not(.active) {
			background-color: #ddd;
		}

		.navbar-expand-lg .navbar-nav .nav-item.active .nav-link {
			color: white;
		}

		img,
		svg {
			fill: var(--bs-nav-link-color);
		}

		.custom-border {
			border-color: #888 !important;
		}

		.custom-input-height {
			height: 50px;
		}

		/* Simple spinner animation */
        .spinner {
            margin: 50px auto;
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .message {
            text-align: center;
            font-family: Arial, sans-serif;
            margin-top: 20px;
        }
	</style>

</head>

<body>

	<header class="header">
		<div class="wrapper">
			<nav class="navbar navbar-expand-lg bg-body-tertiary" data-bs-theme="dark">
				<div class="container-fluid">
					<img height="40px" src="img/building_greyscale.png" style="padding-right:10px;"><img height="30px"
						src="img/LeaseManager.png">
				</div>
			</nav>
			</wrapper>
	</header>

	<div class="content" style="width:50%;">
		<div class="page-title">
			<div class="icon">
				<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg\"
					viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
					<path
						d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z" />
				</svg>
			</div>
			<div class="wrap">
				<h2>Pay OPEX Bill</h2>
			</div>
		</div>

		<div class="block">
			<form method="post"
				action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . '?invoiceid=' . $QPinvoiceid); ?>">
				</formclass>

				<div class="row">
					<div class="col-md-3">
						<h6><?php echo "Supplier: "; ?></h6>
						<h6><?php echo "Invoice Number: "; ?></h6>
						<h6><?php echo "Date: "; ?></h6>
						<h6><?php echo "Due Date: "; ?></h6>
						<h6><?php echo "Amount: "; ?></h6>
					</div>
					<div class="col-md-9">
						<h6><?php echo $companyname; ?></h6>
						<h6><?php echo $invoicenumber; ?></h6>
						<h6><?php echo date_format(date_create($invoicedate), "j F Y"); ?></h6>
						<h6><?php echo date_format(date_create($invoiceduedate), "j F Y"); ?></h6>
						<h6><?php echo "$".$invoicetotal; ?></h6>
					</div>
				</div>
				<?php
				if ($_SERVER["REQUEST_METHOD"] == "POST" and $bankaccountErr == NULL and $bankaccountnumberErr == NULL and $invoicetotalErr == NULL and $theirparticularsErr == NULL and $theircodeErr == NULL and $theirreferenceErr == NULL and $yourcodeErr == NULL and $yourreferenceErr == NULL) {
					
					if (isset($parsedQuery['error'])) {
						echo "Error: " . $parsedQuery['error'] . "<br>";
					}
					
					if (isset($parsedQuery['error_description'])) {
						echo "Error Description: " . $parsedQuery['error_description'] . "<br>";
					}
					//Make the payment through Akahu
					// Write an entry to the cron log file before we start
    				$logFile = LOG_FILE_PATH;
    				$dateNow = new DateTime('now', new DateTimeZone('Pacific/Auckland'));
    				$currentDateTime = $dateNow->format('Y-m-d H:i:s');
					$currentDate = $dateNow->format('Y-m-d');

    				$logMessage = "Making Akahu payment: $currentDateTime";
    				file_put_contents($logFile, $logMessage, FILE_APPEND);

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

							$paymentdata = [
								"to" => [
								  "account_number" => $bankaccountnumber,
								  "name" => $companyname
								],
								"from" => $_id,
								"amount" => $invoicetotal,
								"meta" => [
								  "source" => [
									"code" => $yourcode,
									"reference" => $yourreference
								  ],
								  "destination" => [
									"particulars" => $theirparticulars,
									"code" => $theircode,
									"reference" => $theirreference
								  ]
								]
							];

							$jsonData = json_encode($paymentdata);
//echo $jsonData . "<br>";
            				$ch = curl_init("https://api.akahu.io/v1/payments");
            				// Set cURL options
            				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
							curl_setopt($ch, CURLOPT_POST, true);
							curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            				curl_setopt($ch, CURLOPT_HTTPHEADER, [
							"Content-Type: application/json",
            				"accept: application/json",
            				"X-Akahu-Id: $appToken",
            				"Authorization: $authorization",
							"Content-Length: " . strlen($jsonData)
            				]);

            				// Execute the payment request
            				$response = curl_exec($ch);
//echo "Response: " . $response . "<br>";
            				// Check for cURL errors
            				if ($response === false) {
                				echo "cURL Error: " . curl_error($ch);
                				//$logMessage = "cURL Error: " . curl_error($ch) . "\n";
            				} else {
                				$data = json_decode($response, true); // Decode as an associative array
                				if (isset($data['message'])) {
                    				if ($data['message'] == "Unauthorized") {

									}
									$logMessage = " - ".$response."\n";
									file_put_contents($logFile, $logMessage, FILE_APPEND);
									exit ($data['message']);
								}
								if (isset($data['item_id'])) {
									$paymentid = $data['item_id'];
									// Log the payment ID
									$logMessage = " - ".$paymentid."\n";
									file_put_contents($logFile, $logMessage, FILE_APPEND);
								}
							}
							curl_close($ch);
						}
					}

					
					?><div class="row" style="padding-top:20px;">
						<div class="col-md-12 border-top" style="padding-top:20px;">
							<div class="mb-5">
								<div class="form-label">

									<!--<p>Success!</p>
									<p>Payment has been sent to your bank for processing.</p>-->

									<?php
//echo "Payment ID: " . $paymentid . "<br>";
									//Check the status of the Akahu payment
									$geturl = "https://api.akahu.io/v1/payments/".$paymentid;
//echo "geturl: " . $geturl . "<br>";
									$ch = curl_init($geturl);
            						curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            						curl_setopt($ch, CURLOPT_HTTPHEADER, [
            						"X-Akahu-Id: $appToken",
            						"Authorization: $authorization"
            						]);

            						// Execute the request
            						$response1 = curl_exec($ch);
//echo "Response1: " . $response1 . "<br>";
									// Check for cURL errors
									if ($response1 === false) {
										echo "cURL Error: " . curl_error($ch);
										//$logMessage = "cURL Error: " . curl_error($ch) . "\n";
									} else {
										$data1 = json_decode($response1, true); // Decode as an associative array
										if (isset($data1['message'])) {
											if ($data1['message'] == "Unauthorized") {
		
											}
											$logMessage = " - ".$response1."\n";
											file_put_contents($logFile, $logMessage, FILE_APPEND);
											exit ($data1['message']);
										}
										if (isset($data1['item'])) {
											$paymentstatus = $data1['item']['status'];
											// Set the invoice status to processing until the webhook post
											$final = $data1['item']['final'];
											//if ($final == 'true') {
											//	$statusid = 4; // Paid
											//} else {
												$statusid = 6; // Processing
											//}
											//Update the invoice
											$stmt = $con->prepare('UPDATE transactions SET invoiceStatusID = ?, akahuPaymentID = ?, invoicePaidDate = ? WHERE idtransaction = ?');
											$stmt->bind_param('issi', $statusid, $paymentid, $currentDate, $QPinvoiceid);
											$stmt->execute();
											$stmt->close();

											// Remove any existing paymentstatus record
											//$stmt = $con->prepare('UPDATE paymentstatus SET akahuPaymentID = NULL, statusCode = NULL, statusText = NULL WHERE opexInvoiceID = ?');
											//$stmt->bind_param('i', $QPinvoiceid);
											//$stmt->execute();
											//$stmt->close();
											$stmt = $con->prepare('DELETE FROM paymentstatus WHERE opexInvoiceID = ?');
											$stmt->bind_param('i', $QPinvoiceid);
											$stmt->execute();
											$stmt->close();
										}
									}
									echo "<div id=\"result\">";
									if ($final == 'true') {
										echo "<p>Payment Completed</p>";
									} else {
										echo "<p>Once we receive confirmation of completion the invoice status will be updated to PAID.</p>";
									}
									echo "</div>";
									curl_close($ch);
									?>

									
								</div>			
							</div>
						</div>
					</div>
					<div class="row">
           				<div class="col-sm-4"><a href="payopexinvoices.php" class="btn btn-primary">Back to Invoices</a></div>
        			</div>
					<?php
				} else {
					if (isset($status_code)) {
						// Display the error message
						echo "<div class=\"col-md-12\">
								<span style=\"color:red; font-weight:500;\">".$status_code.": ".$status_text."</span>
						</div>";
					} else if ($invoicestatusid == 9) {
						// Display the status (Pending approval)
						echo "<div class=\"col-md-12\">
								<span style=\"color:red; font-weight:500;\">".$invoicestatus."</span>
						</div>";
					}
					?>
				<div class="row" style="padding-top:20px;">
					<div class="col-md-6 border-top" style="padding-top:20px;">
						<div class="mb-5">
							<div class="form-label" style="font-weight:500;">From</div>
							<select class="form-control custom-border custom-input-height" id="_id" name="_id">
								<?php
								echo "<option value=\"0\"> - Select a Bank Account - </option>";
								while ($row2 = $result2->fetch_assoc()) {
									if ($row2["_id"] == $_id) {
										echo "<option value=\"" . $row2["_id"] . "\" selected>" . $row2["name"] . " - " . $row2["formatted_account"] . "</option>";
									} else {
										echo "<option value=\"" . $row2["_id"] . "\">" . $row2["name"] . " - " . $row2["formatted_account"] . "</option>";
									}
								}
								?>
							</select>
							<div class="col-sm-12"><span class="error"><span
										class="text-danger"><?php echo $bankaccountErr; ?></span></div>
						</div>
					</div>
					<div class="col-md-6 border-top" style="padding-top:20px;">
						<div class="mb-5">
							<div class="form-label" style="font-weight:500;">To</div>

							<div class="mb-3">
								<input type="text" class="form-control custom-border custom-input-height"
									id="companyname" name="companyname" readonly value="<?php echo $companyname . " - " . $bankaccountnumber;?>">
								<div class="col-sm-12"><span class="error"><span
											class="text-danger"><?php echo $bankaccountnumberErr; ?></span></div>
							</div>

							<input type="text" id="companyid" name="companyid" hidden value="<?php echo $invoicecompanyid;?>">
							<div class="col-sm-12"><span class="error"><span
										class="text-danger"><?php echo $payeebankaccountErr; ?></span></div>
						</div>
					</div>
				</div>
				
					<div class="row">
						<div class="col-md-4">
							<div class="mb-3">
								<label for="input1" class="form-label" style="font-weight:500;">Amount</label>
								<input type="text" class="form-control custom-border custom-input-height" id="invoicetotal"
									name="invoicetotal" value="<?php echo $invoicetotal; ?>" readonly>
								<div class="col-sm-12"><span class="error"><span
											class="text-danger"><?php echo $invoicetotalErr; ?></span></div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-12" style="padding-top:20px; font-weight:500;">Their statement</div>
					</div>

					<div class="row">
						<div class="col-md-4">
							<div class="mb-3">
								<label for="theirparticulars" class="form-label">Particulars</label>
								<input type="text" class="form-control custom-border custom-input-height"
									id="theirparticulars" name="theirparticulars" value="<?php echo $theirparticulars; ?>"
									maxlength="12">
								<div class="col-sm-12"><span class="error"><span
											class="text-danger"><?php echo $theirparticularsErr; ?></span></div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="mb-3">
								<label for="theircode" class="form-label">Code</label>
								<input type="text" class="form-control custom-border custom-input-height" id="theircode"
									name="theircode" value="<?php echo $theircode; ?>" maxlength="12">
								<div class="col-sm-12"><span class="error"><span
											class="text-danger"><?php echo $theircodeErr; ?></span></div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="mb-3">
								<label for="theirreference" class="form-label">Reference</label>
								<input type="text" class="form-control custom-border custom-input-height"
									id="theirreference" name="theirreference" value="<?php echo $theirreference; ?>"
									maxlength="12">
								<div class="col-sm-12"><span class="error"><span
											class="text-danger"><?php echo $theirreferenceErr; ?></span></div>
							</div>
						</div>
					</div>


					<div class="row">
						<div class="col-sm-12" style="padding-top:20px; font-weight:500;">Your statement</div>
					</div>

					<div class="row">
						<div class="col-md-4">
							<div class="mb-3">
								<label for="yourparticulars" class="form-label">Particulars</label>
								<input type="text" class="form-control custom-border custom-input-height"
									id="yourparticulars" readonly placeholder="Reserved for use by Akahu">
							</div>
						</div>
						<div class="col-md-4">
							<div class="mb-3">
								<label for="yourcode" class="form-label">Code</label>
								<input type="text" class="form-control custom-border custom-input-height" id="yourcode"
									name="yourcode" value="<?php echo $yourcode; ?>" maxlength="12">
								<div class="col-sm-12"><span class="error"><span
											class="text-danger"><?php echo $yourcodeErr; ?></span></div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="mb-3">
								<label for="yourreference" class="form-label">Reference</label>
								<input type="text" class="form-control custom-border custom-input-height" id="yourreference"
									name="yourreference" value="<?php echo $invoicenumber; ?>" maxlength="12">
								<div class="col-sm-12"><span class="error"><span
											class="text-danger"><?php echo $yourreferenceErr; ?></span></div>
							</div>
						</div>
					</div>

					<div class="row" style="padding-top:60px;">
						<div class="col-sm-6" style="text-align:right;">
							<a href="payopexinvoices.php" class="btn btn-outline-dark">Back to Opex Invoices</a>
						</div>
						<div class="col-sm-6">
							<input type="submit" value="Pay" class="btn btn-primary" style="width:100px">
						</div>
					</div>
				</form>
				<?php
				}
				?>
			<script>
				function closeWindow() {
					window.close();
				}
			</script>
		</div>