<?php
// Include the main.php file
include 'main.php';
// Check if the user is logged in, if not then redirect to login page
check_loggedin($con);
// Template code below

$accountid = $_SESSION['account_id'];

$sqlAccess = "SELECT * FROM accesscontrol WHERE accountID = $accountid";
$resultAccess = $con->query($sqlAccess);

$accessto = -1;

if ($resultAccess->num_rows > 0) {
    while($rowAccess = $resultAccess->fetch_assoc()) {
       $accessto .= "," . $rowAccess["companyID"]; 
    }
}

$Q = explode("/", $_SERVER['QUERY_STRING']);
parse_str($Q[0],$QueryParameters);
$QPinvoiceid = $QueryParameters['invoiceid'];

//Get the invoice
$sql = "SELECT * FROM invoices_view WHERE idinvoice = $QPinvoiceid and recordOwnerID IN ($accessto)";
$result = $con->query($sql);
//Get the invoice items
$sql1 = "SELECT * FROM invoiceitems_view WHERE invoiceid = $QPinvoiceid and recordOwnerID IN ($accessto) ORDER BY idinvoiceitem";
$result1 = $con->query($sql1);

if ($result->num_rows > 0) {
    $companyaddresscontent = "";
    // output data of each row
    while($row = $result->fetch_assoc()) {
        $invoicenumber = $row["invoicenumber"];
        $invoicedate = date_format(date_create($row["invoicedate"]),"j F Y");
        if ($row["companyname"] <> "") {
            $companyaddresscontent .= $row["companyname"]."<br>";
        }
        if ($row["companyaddress1"] <> "") {
            $companyaddresscontent .= $row["companyaddress1"]."<br>";
        }
        if ($row["companyname"] <> "") {
            $companyaddresscontent .= $row["companyaddress2"]."<br>";
        }
        if ($row["companyaddress2"] <> "") {
            $companyaddresscontent .= $row["companysuburb"]."<br>";
        }
        if ($row["companycity"] <> "") {
            $companyaddresscontent .= $row["companycity"]."<br>";
        }
        if ($row["companypostcode"] <> "") {
            $companyaddresscontent .= $row["companypostcode"];
        }
        $invoicerecordownerid = $row["recordownerid"];
        $invoiceamount = $row["invoiceamount"];
        $invoicegst = $row["invoicegst"];
        $invoicetotal = $row["invoicetotal"];
        $invoiceduedate = date_format(date_create($row["invoiceduedate"]),"j F Y");
        $invoicestatus = $row["invoicestatus"];
    }
}

//Get the company address details
$sql2 = "SELECT companyName, address1, address2, addressSuburb, addressCity, addressPostCode, gstNumber FROM companies WHERE idcompany = $invoicerecordownerid";
$result2 = $con->query($sql2);
if ($result2->num_rows > 0) {
    $addresscontent = "";
    // output data of each row
    while($row2 = $result2->fetch_assoc()) {
        if ($row2["address1"] <> "") {
            $addresscontent .= $row2["address1"]."<br>";
        }
        if ($row2["address2"] <> "") {
            $addresscontent .= $row2["address2"]."<br>";
        }
        if ($row2["addressSuburb"] <> "") {
            $addresscontent .= $row2["addressSuburb"]."<br>";
        }
        if ($row2["addressCity"] <> "") {
            $addresscontent .= $row2["addressCity"]."<br>";
        }
        if ($row2["addressPostCode"] <> "") {
            $addresscontent .= $row2["addressPostCode"];
        }
        $gstnumber = $row2["gstNumber"];
        $invoicefrom = $row2["companyName"];
    }
}

if ($result1->num_rows > 0) {
    $tablecontents = "";

    // output data of each row
    while($row1 = $result1->fetch_assoc()) {
        $tablecontents .= "<tr><td class=\"line\">" . $row1["invoiceitemdescription"] . "</td>";
        $tablecontents .= "<td class=\"line\" style=\"text-align:center\">" . $row1["invoiceitemquantity"] . "</td>";
        $tablecontents .= "<td class=\"line\" style=\"text-align:right\">" . $row1["invoiceitemprice"] . "</td>";
        $tablecontents .= "<td class=\"line\" style=\"text-align:right\">" . $row1["invoiceitemsubtotal"] . "</td></tr>";
    }
    $tablecontents .= "<tr><td colspan=\"3\" style=\"text-align:right; border-bottom:0px;\";>Subtotal</td>";
    $tablecontents .= "<td style=\"text-align:right; border-bottom:0px;\">" . $invoiceamount . "</td></tr>";
    $tablecontents .= "<tr><td colspan=\"2\" style=\" border-bottom:0px;\">&nbsp;</td>";
    $tablecontents .= "<td style=\"text-align:right; border-top:2px; border-color:black;\";>GST</td>";
    $tablecontents .= "<td style=\"text-align:right; border-top:2px; border-color:black;\">" . $invoicegst . "</td></tr>";
    $tablecontents .= "<tr><td colspan=\"3\" style=\"text-align:right; font-weight:bold; border-bottom:0px;\";>Total</td>";
    $tablecontents .= "<td style=\"text-align:right; font-weight:bold; border-bottom:0px;\">" . $invoicetotal . "</td></tr>";
}





require 'vendor/autoload.php';
// reference the Dompdf namespace
use Dompdf\Dompdf;
// instantiate and use the dompdf class
$dompdf = new Dompdf();
$options = $dompdf->getOptions();
$options->setDefaultFont('Arial');
$dompdf->setOptions($options);
$dompdf->loadHtml('
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Invoice</title>
    <meta name="author" content="Barry Pyle" />
    <style type="text/css">
        * {
            margin: 0;
            padding: 0;
            text-indent: 0;
        }

        h1 {
            color: black;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            font-style: normal;
            font-weight: bold;
            text-decoration: none;
            font-size: 15pt;
        }

        .p, p, body {
            color: black;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            font-style: normal;
            font-weight: normal;
            text-decoration: none;
            font-size: 10pt;
            margin: 0pt;
        }

        b, strong {
            font-weight: $font-weight-bolder;
        }

        h3 {
            color: black;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            font-style: normal;
            font-weight: bold;
            text-decoration: none;
            font-size: 10pt;
        }

        h2 {
            color: black;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            font-style: normal;
            font-weight: bold;
            text-decoration: none;
            font-size: 11pt;
        }

        .s1 {
            color: black;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            font-style: italic;
            font-weight: normal;
            text-decoration: none;
            font-size: 10pt;
        }

        li {
            display: block;
        }

        #l1 {
            padding-left: 0pt;
            counter-reset: c1 1;
        }

        #l1>li>*:first-child:before {
            counter-increment: c1;
            content: counter(c1, upper-latin)". ";
            color: black;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            font-style: normal;
            font-weight: normal;
            text-decoration: none;
            font-size: 10pt;
            padding-right: 18px;
        }

        #l1>li:first-child>*:first-child:before {
            counter-increment: c1 0;
        }

        #l2 {
            padding-left: 0pt;
            counter-reset: c2 1;
        }

        #l2>li>*:first-child:before {
            counter-increment: c2;
            content: counter(c2, decimal)" ";
            color: black;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            font-style: normal;
            font-weight: bold;
            text-decoration: none;
            font-size: 11pt;
            padding-right: 20px;
        }

        #l2>li:first-child>*:first-child:before {
            counter-increment: c2 0;
        }

        #l3 {
            padding-left: 0pt;
            counter-reset: c3 1;
        }

        #l3>li>*:first-child:before {
            counter-increment: c3;
            content: counter(c2, decimal)"." counter(c3, decimal)" ";
            color: black;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            font-style: normal;
            font-weight: bold;
            text-decoration: none;
            font-size: 10pt;
            padding-right: 10px;
        }

        #l3>li:first-child>*:first-child:before {
            counter-increment: c3 0;
        }

        #l4 {
            padding-left: 0pt;
            counter-reset: d1 1;
        }

        #l4>li>*:first-child:before {
            counter-increment: d1;
            content: "(" counter(d1, lower-latin)") ";
            color: black;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            font-style: normal;
            font-weight: normal;
            text-decoration: none;
            font-size: 10pt;
            padding-right: 15px;
        }

        #l4>li:first-child>*:first-child:before {
            counter-increment: d1 0;
        }

        #l5 {
            padding-left: 0pt;

            counter-reset: e1 1;
        }

        #l5>li>*:first-child:before {
            counter-increment: e1;
            content: "(" counter(e1, lower-latin)") ";
            color: black;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            font-style: normal;
            font-weight: normal;
            text-decoration: none;
            font-size: 10pt;
            padding-right: 15px;
        }

        #l5>li:first-child>*:first-child:before {
            counter-increment: e1 0;
        }

        td, th {
            vertical-align: middle;
            border-bottom: solid;
            border-bottom-color: rgb(222, 226, 230);
            border-bottom-width: 1.11111px;
            border-left-width: 0;
            border-right-width: 0;
            padding:10px;
        }

        #highlight {
            color:orange;
        }

        @media screen {
            p.page-number {
                display:none;
            }
        }

    </style>
</head>
<body>
<table style="width:700px; margin-left:auto; margin-right:auto;">
    <tr>
        <td colspan="3" style="padding: 40px 5px;">
            <p style="font-size:26px; text-indent:0pt; text-align:right;">'.$invoicefrom.'</p>
        </td>
    </tr>
    <tr>
        <td style="width:55%; vertical-align:top; border-color:white">
            <p style="font-size:26px; padding-bottom:10px;">TAX INVOICE</p>
            <p style="padding-left:10px">'.$companyaddresscontent.'</p>
        </td>
        <td style="width:25%; vertical-align:top; border-color:white">
            <p><b>Invoice Date</b></p><p style="padding-bottom:10px;">'.$invoicedate.'</p>
            <p><b>Invoice Number</b></p><p style="padding-bottom:10px;">'.$invoicenumber.'</p>
            <p><b>GST Number</b></p><p>'.$gstnumber.'</p>
        </td>
        <td style="width:20%; vertical-align:top; border-color:white">
            <p>'.$addresscontent.'</p>
        </td>
    </tr>
    <tr>
        <td colspan="3" style="padding-top:100px; border-color:white">
        <table class="table" style="width:100%">
        <thead>
        <tr>
            <th class=\"line\" style="width:55%; text-align:left;"><p><b>Description</b></p></th>
            <th class=\"line\" style="width:15%; text-align:center"><p><b>Quantity</b></p></th>
            <th class=\"line\" style="width:15%; text-align:right"><p><b>Price</b></p></th>
            <th class=\"line\" style="width:15%; text-align:right"><p><b>Amount</b></p></th>
        </tr>
        </thead>
        <tbody id="myTable">'
        .$tablecontents.
        '</tbody>
        </table>
        </td>
    </tr>
</body>
</html>
');
// (Optional) Setup the paper size and orientation
$dompdf->setPaper('A4', 'portrait');
// Render the HTML as PDF
$dompdf->render();
// Output the generated PDF to Browser
$dompdf->stream();

$con->close();