<?php
//Thi page is designed to be run by a scheduler not a user.
//Access this page using an authorisation token.
$authorised_key = '370e86358956424a1433a9d1812bc5e5dd210622879358880e301050da049eeb';
if (!isset($_GET['key']) || $_GET['key'] !== $authorised_key) {
    die("Unouthorised access.");
} else {

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset=\"utf-8\">
	<meta name=\"viewport\" content=\"width=device-width,minimum-scale=1\">
	<title>Auto Create Tenant Invoices</title>
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
		<h2>Auto Create Tenant Invoices for ".date('F')."</h2>
	</div>
</div>
<div class=\"block\">

<table class=\"table table-striped\">";

// Include the configuration file
include_once 'config.php';
// We need to use sessions, so you should always start sessions using the below function
session_start();
// Connect to the MySQL database using MySQLi
$con = mysqli_connect(db_host, db_user, db_pass, db_name);
// If there is an error with the MySQL connection, stop the script and output the error
if (mysqli_connect_errno()) {
	exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}


    $sql = "SELECT * FROM leases_view_with_current_rent WHERE leasestatusid = 2 and leaseexpirydate > NOW() ORDER BY tenantname";
    $result = $con->query($sql);

    $invoicedate = date('Y-m-d', mktime(0, 0, 0, date('m'), 1, date('Y')));
    $invoiceduedate = date('Y-m-d', strtotime('+19 days', strtotime($invoicedate)));
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
                $thisrecordowner = $row["recordownerid"]; //Get the new record owner id from their last invoice.
                //Get the last invoice by this user's company to get the next invoice number.
                $sql1 = "SELECT invoiceNumber FROM invoices WHERE recordOwnerID = $thisrecordowner ORDER BY invoiceNumber DESC LIMIT 1";
                $result1 = $con->query($sql1);
                while($row1 = $result1->fetch_assoc()) {
                    $lastinvoicenumber = $row1["invoiceNumber"]; //Get the invoice number text
                    $invoicenumberint = (int)ltrim($row1["invoiceNumber"],"INV-") + 1; //Get the number adn add 1 to it
                }
            }

            $invoicenumber = "INV-". str_pad($invoicenumberint,4,"0",STR_PAD_LEFT); //Concatenate the Invoice Number.
            $invoicecompanyid = $row["tenantid"];
            $invoiceitemprice = round(($row["currentrentpremises"] + $row["currentrentcarparks"]) / 12, 2, PHP_ROUND_HALF_UP);
            $invoiceitemtax = round($invoiceitemprice * 0.15,2,PHP_ROUND_HALF_UP);
            $invoiceitemsubtotal = $invoiceitemprice;
            $invoiceitemtotal = $invoiceitemprice + $invoiceitemtax;
            $invoicestatusid = 2; //Active
            $recordownerid = $thisrecordowner; //The owner of the lease
            $invoiceitemdescription = date('F')." rent for ". $row["unitname"] . ", " . $row["premisesaddress1"];
            $invoiceitemquantity = $invoicecategoryid = 1;
            
            $stmt = $con->prepare("INSERT INTO invoices (invoiceNumber, invoiceCompanyID, invoiceDate, invoiceDueDate, invoiceStatusID, recordOwnerID) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sissii", $invoicenumber, $invoicecompanyid, $invoicedate, $invoiceduedate, $invoicestatusid, $recordownerid);
            if ($stmt->execute()) {
                $last_id = $con->insert_id;
                $stmt1 = $con->prepare("INSERT INTO invoiceitems (invoiceID, invoiceItemDescription, invoiceItemQuantity, invoiceItemPrice, invoiceCategoryID, invoiceItemSubtotal, invoiceItemTax, invoiceItemTotal, recordOwnerID) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt1->bind_param("isddidddi", $last_id, $invoiceitemdescription, $invoiceitemquantity, $invoiceitemprice, $invoicecategoryid, $invoiceitemsubtotal, $invoiceitemtax, $invoiceitemtotal, $recordownerid);
                if ($stmt1->execute()) {
                    echo "<tr>
                    <td>".$invoicedate."</td>
                    <td>".$row["tenantname"].$invoicecompanyid."</td>
                    <td>".$invoicenumber."</td>
                    <td>" . date('F')." rent for ". $row["unitname"] . ", " . $row["premisesaddress1"] ."</td>
                    </tr>";
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
            $sql3 = "UPDATE invoices SET invoiceAmount = '$invoiceamount', invoiceGST = '$invoicegst', invoiceTotal = '$invoicetotal' WHERE idinvoice = $last_id";
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
    }

echo "</table>
</div>
</body>
</html>";
}