<?php
//This page is designed to be run by a scheduler not a user.

//Access this page using an authorisation token.
$cron_token = '370e86358956424a1433a9d1812bc5e5dd210622879358880e301050da049eeb';
// Check if the request contains the correct token 
if (php_sapi_name() !== 'cli' && (!isset($_GET['token']) || $_GET['token'] !== $cron_token)) { 
    http_response_code(403); // Forbidden 
    die('Authorisation denied.'); 
} else {

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset=\"utf-8\">
	<meta name=\"viewport\" content=\"width=device-width,minimum-scale=1\">
	<title>Auto Create Rent Invoices</title>
	<link href=\"style.css\" rel=\"stylesheet\" type=\"text/css\">
	<link href=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css\" rel=\"stylesheet\">
	<link href=\"https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css\" rel=\"stylesheet\">
    <script src=\"https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js\"></script>
</head>
<body>

<div class=\"page-title\">
	<div class=\"icon\">
		<svg width=\"20\" height=\"20\" xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 576 512\"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d=\"M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z\"/></svg>
	</div>	
	<div class=\"wrap\">
		<h2>Auto Create Rent Invoices</h2>
	</div>
</div>
<div class=\"block\">

<table class=\"table table-striped\">";


// We need to use sessions, so you should always start sessions using the below function
session_start();
// Connect to the MySQL database using MySQLi
$con = mysqli_connect(db_host, db_user, db_pass, db_name);
// If there is an error with the MySQL connection, stop the script and output the error
if (mysqli_connect_errno()) {
	exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}

//Get the day of the month.
$dayOfMonth = date ("d");

if ($dayOfMonth == 20) {
    $sql = "SELECT * FROM leases_view_with_current_rent WHERE leasestatusid = 2 and leaseexpirydate > NOW() and invoicedate in (0,20) ORDER BY tenantname";
    $result = $con->query($sql);
} else {
    $sql = "SELECT * FROM leases_view_with_current_rent WHERE leasestatusid = 2 and leaseexpirydate > NOW() and invoicedate = $dayOfMonth ORDER BY tenantname";
    $result = $con->query($sql);
}
    
    $invoicenumberint = 0;
    $thisrecordowner = 0;

    if ($result->num_rows > 0) {
        // output data of each row
        while($row = $result->fetch_assoc()) {

            if ($thisrecordowner === $row["recordownerid"]) {
                //same invoice record owner.
                $invoicenumberint += 1;
            } else {
                //New or the first invoice record owner.
                $thisrecordowner = $row["recordownerid"]; //Set the record owner from the lease.
                //Get the last invoice by this user's company to get the next invoice number.
                $sql1 = "SELECT invoiceNumber FROM transactions_view WHERE transactiontypeid = 1 and recordOwnerID = $thisrecordowner ORDER BY invoiceNumber DESC LIMIT 1";
                $result1 = $con->query($sql1);
                while($row1 = $result1->fetch_assoc()) {
                    $lastinvoicenumber = $row1["invoiceNumber"]; //Get the invoice number text.
                    $invoicenumberint = (int)ltrim($row1["invoiceNumber"],"INV-") + 1; //Get the number and add 1 to it.
                }
            }

            switch ($row["invoicedate"]) {
                case 0:  //20th of the month prior.
                    $transactiondate = date('Y-m-d', mktime(0, 0, 0, date('m'), 20, date('Y')));
                    $invoiceduedate = date('Y-m-d', strtotime('+19 days', strtotime($transactiondate)));
                    $date=date_create(); //Today.
                    date_add($date,date_interval_create_from_date_string("1 months"));  //Next month
                    $invoiceitemdescription = date_format($date,'F')." rent for ". $row["unitname"] . ", " . $row["premisesaddress1"];
                    break;
                    
                case 1:  //1st of the month.
                    $transactiondate = date('Y-m-d', mktime(0, 0, 0, date('m'), 1, date('Y')));
                    $invoiceduedate = date('Y-m-d', strtotime('+19 days', strtotime($transactiondate)));
                    $invoiceitemdescription = date('F')." rent for ". $row["unitname"] . ", " . $row["premisesaddress1"];
                    break;
                
                default:  //Anniversary of the commencement date.
                    $transactiondate = date('Y-m-d', mktime(0, 0, 0, date('m'), date('j'), date('Y')));
                    $invoiceduedate = date('Y-m-d', strtotime('+10 days', strtotime($transactiondate)));
                    $date=date_create(); //Today.
                    date_add($date,date_interval_create_from_date_string("1 months - 1 day"));  //One day less next month.
                    // Rent from [today] to [one day less next month]...
                    $invoiceitemdescription = "Rent from " . date("j/m/y") . " to " . date_format($date,"j/m/y") . "<br> for ". $row["unitname"] . ", " . $row["premisesaddress1"];
            }

            $invoicenumber = "INV-". str_pad($invoicenumberint,4,"0",STR_PAD_LEFT); //Concatenate the Invoice Number.
            $transactioncompanyid = $row["tenantid"];
            $invoiceitemprice = round(($row["currentrentpremises"] + $row["currentrentcarparks"]) / 12, 2, PHP_ROUND_HALF_UP);
            $invoiceitemtax = round($invoiceitemprice * 0.15,2,PHP_ROUND_HALF_UP);
            $invoiceitemsubtotal = $invoiceitemprice;
            $invoiceitemtotal = $invoiceitemprice + $invoiceitemtax;
            $invoicestatusid = 2; //Active
            $recordownerid = $thisrecordowner; //The owner of the lease
            $invoiceitemquantity = $invoicecategoryid = 1;
            $transactioncategoryid = 1;
            $invoiceitempremises = $row["unitname"];
            $invoiceitempremisesID = $row["premisesid"];
            
            $stmt = $con->prepare("INSERT INTO transactions (invoiceNumber, transactionCompanyID, transactionDate, transactionAmount, trnsactionGST, transactionTotal, transactionCategoryID, invoiceDueDate, invoiceStatusID, premisesID, recordOwnerID) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sisdddisiii", $invoicenumber, $transactioncompanyid, $transactiondate, $invoiceitemprice, $invoiceitemtax, $invoiceitemtotal, $transactioncategoryid, $invoiceduedate, $invoicestatusid, $invoiceitempremisesID, $recordownerid);
            if ($stmt->execute()) {
                $last_id = $con->insert_id;
                $stmt1 = $con->prepare("INSERT INTO invoiceitems (invoiceID, invoiceItemDescription, invoiceItemPremises, invoiceItemQuantity, invoiceItemPrice, invoiceCategoryID, invoiceItemSubtotal, invoiceItemTax, invoiceItemTotal, recordOwnerID) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt1->bind_param("issddidddi", $last_id, $invoiceitemdescription, $invoiceitempremises, $invoiceitemquantity, $invoiceitemprice, $invoicecategoryid, $invoiceitemsubtotal, $invoiceitemtax, $invoiceitemtotal, $recordownerid);
                if ($stmt1->execute()) {
                    echo "<tr>
                    <td>".$transactiondate."</td>
                    <td>".$row["tenantname"]."</td>
                    <td>".$invoicenumber."</td>
                    <td>".$invoiceitemdescription."</td>
                    </tr>";
                } else {
                    $logMessage = "Invoice Item creation failed (".$invoicenumber."). \n";
                    file_put_contents($logFile, $logMessage, FILE_APPEND);
                }
                // Create the public invoice token
                function uuid4() {
                    /* 32 random HEX + space for 4 hyphens */
                    $out = bin2hex(random_bytes(18));

                    $out[8]  = "-";
                    $out[13] = "-";
                    $out[18] = "-";
                    $out[23] = "-";

                    /* UUID v4 */
                    $out[14] = "4";
                    
                    /* variant 1 - 10xx */
                    $out[19] = ["8", "9", "a", "b"][random_int(0, 3)];

                    return $out;
                }
                $token = uuid4();
                $stmt = $con->prepare("INSERT INTO public_invoice_links (transactionID, token) VALUES (?, ?)");
                $stmt->bind_param("is", $last_id, $token);
                if ($stmt->execute()) {
                    $logMessage = "Rent invoice created (INV-".$invoicenumber."). \n";
                    file_put_contents($logFile, $logMessage, FILE_APPEND);
                } else {
                    echo 'Error creating public invoice token: ' . $con->error;
                }
            }
            $sql2 = "SELECT * FROM invoices_sum_items_view WHERE invoiceid = $last_id";
            $result2 = $con->query($sql2);
            while($row2 = $result2->fetch_assoc()) {
                $invoiceamount = $row2["sumofsubtotal"];
                $invoicegst = $row2["sumoftax"];
                $invoicetotal = $row2["sumoftotal"];
            }
            //Update the invoice record with the Subtotal, GST and Total
            $sql3 = "UPDATE transactions SET transactionAmount = '$invoiceamount', transactionGST = '$invoicegst', transactionTotal = '$invoicetotal' WHERE idtransaction = $last_id";
            if ($con->query($sql3) === TRUE) {
                echo '<table class="table table-hover">
                <tbody>
                    <tr class="success">
                        <td>Success!</td>
                    </tr>
                </tbody>
                </table>';
            } else {
                echo 'Error updating record: ' . $con->error;
            }
        }
        $logMessage = "Rent invoices created successfully. \n";
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    } else {
        echo "0 invoices scheduled for creation";
        $logMessage = "0 invoices scheduled for creation - \n";
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }

echo "</table>
</div>
</body>
</html>";
}