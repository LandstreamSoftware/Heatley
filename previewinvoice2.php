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

?>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Invoice</title>
    <meta name="author" content="Lease Manager" />
    <style type="text/css">
        @page {
            size: A4;
            margin: 15mm;
        }

        * {
            margin: 0;
            padding: 0;
            text-indent: 0;
        }

        .content {
            min-height: 100vh;
            padding-bottom: 50px;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            
            font-size: 12px;
            padding: 0;
            border-top: 1px solid #ccc;
        }

        /* Ensure the footer stays at the bottom in print */ 
        @media print { 
            html, body { 
                height: 100%; /* Full height layout */ 
            } .content { 
                padding-bottom: 0; 
                box-sizing: border-box; 
            } .footer { 
                position: fixed; 
                bottom: 0; 
            }
        }

        h1 {
            color: black;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-style: normal;
            font-weight: bold;
            text-decoration: none;
            font-size: 15pt;
        }

        .p, p, body {
            color: black;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-style: normal;
            font-weight: normal;
            text-decoration: none;
            font-size: 12pt;
            margin: 0pt;
        }

        b, strong {
            font-weight: bolder;
        }

        h3 {
            color: black;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-style: normal;
            font-weight: bold;
            text-decoration: none;
            font-size: 10pt;
        }

        h2 {
            color: black;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-style: normal;
            font-weight: bold;
            text-decoration: none;
            font-size: 11pt;
        }

        .s1 {
            color: black;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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

<?php

$Q = explode("/", $_SERVER['QUERY_STRING']);
parse_str($Q[0],$QueryParameters);
$QPinvoiceid = $QueryParameters['invoiceid'];

//Get the invoice
$sql = "SELECT * FROM transactions_view WHERE idtransaction = $QPinvoiceid and recordOwnerID IN ($accessto)";
$result = $con->query($sql);
//Get the invoice items
$sql1 = "SELECT * FROM invoiceitems_view WHERE invoiceid = $QPinvoiceid and recordOwnerID IN ($accessto) ORDER BY idinvoiceitem";
$result1 = $con->query($sql1);

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        $invoicenumber = $row["invoicenumber"];
        $transactiondate = date_format(date_create($row["transactiondate"]),"d/m/Y");
        $companyname = $row["companyname"];
        $companyaddress1 = $row["companyaddress1"];
        $companyaddress2 = $row["companyaddress2"];
        $companysuburb = $row["companysuburb"];
        $companycity = $row["companycity"];
        $companypostcode = $row["companypostcode"];
        $invoicerecordownerid = $row["recordownerid"];
        $transactionamount = $row["transactionamount"];
        $transactiongst = $row["transactiongst"];
        $transactiontotal = $row["transactiontotal"];
        $invoiceduedate = date_format(date_create($row["invoiceduedate"]),"d/m/Y");
        $invoicestatus = $row["invoicestatus"];
        if (isset($row["reconciledamount"])) {
            $reconciledamount = $row["reconciledamount"];
        } else {
            $reconciledamount = 0;
        }
        if (isset($row["balanceowing"])) {
            $balanceowing = $row["balanceowing"];
        } else {
            $balanceowing = $row["transactiontotal"];
        }
    }
}

//Get the branding company id
$sql2 = "SELECT * FROM companies WHERE idcompany = $invoicerecordownerid";
$result2 = $con->query($sql2);
if ($result2->num_rows > 0) {
    // output data of each row
    while($row2 = $result2->fetch_assoc()) {
        if ($row2["propertyManagerCompanyID"] == 0) {
            $propertymanagercompanyid = $invoicerecordownerid;
        } else {
            $propertymanagercompanyid = $row2["propertyManagerCompanyID"];
        }
        
    }
}

//Get the property managers company's logo
$sql3 = "SELECT * FROM companies WHERE idcompany = $propertymanagercompanyid"; 
$result3 = $con->query($sql3);
if ($result3->num_rows > 0) {
    // output data of each row
    while($row3 = $result3->fetch_assoc()) {
        $address1 = $row3["address1"];
        $address2 = $row3["address2"];
        $addresssuburb = $row3["addressSuburb"];
        $addresscity = $row3["addressCity"];
        $addresspostcode = $row3["addressPostCode"];
        $gstnumber = $row3["gstNumber"];
        $bankaccountnumber = $row3["bankAccountNumber"];
        $logoimagefilepath = "/img/company_logos/" . $row3["logoImageFileName"];
    }
}
?>

<body>

<table style="width:780px; margin-left:auto; margin-right:auto;">
    <tr>
        <td colspan="3" style="padding: 0 0 10px 5px;">
            <img src="<?php echo $logoimagefilepath ?>" style="width:300px; float:right; padding-bottom:10px;">
        </td>
    </tr>
    <tr>
        <td style="width:55%; vertical-align:top; border-color:white">
            <p style="font-size:26px; padding-left:5px">TAX INVOICE</p><br>
            <p style="padding-left:10px"><?php echo $companyname?><br>
            <?php echo $companyaddress1?><br>
            <?php echo $companyaddress2?><br>
            <?php echo $companysuburb?><br>
            <?php echo $companycity." ".$companypostcode?><br>
            </p>
        </td>
        <td style="width:25%; vertical-align:top; border-color:white">
            <p><b>Invoice Date</b><br><?php echo $transactiondate ?></p></br>
            <p><b>Invoice Number</b><br><?php echo $invoicenumber ?></p></br>
  <!--          <p><b>GST Number</b><br><?php echo $gstnumber ?></p>   -->
        </td>
        <td style="width:25%; vertical-align:top; text-align:right; border-color:white">
            <p><?php echo $address1?><br>
            <?php if ($address2 <> "") {
                echo $address2."<br>";
            }
            if ($addresssuburb <> "") {
                echo $addresssuburb."<br>";
            }?>
            <?php echo $addresscity." ".$addresspostcode?><br></p>
        </td>
    </tr>
    <tr>
        <td colspan="3" style="padding-top:100px; border-color:white">
        <table class="table" style="width:100%">
        <thead>
        <tr>
            <th style="width:55%; text-align:left"><p><b>Description</b></p></th>
            <th style="width:15%; text-align:center"><p><b>Quantity</b></p></th>
            <th style="width:15%; text-align:right"><p><b>Price</b></p></th>
            <th style="width:15%; text-align:right"><p><b>Amount</b></p></th>
        </tr>
        </thead>
        <tbody id="myTable">
        <?php
        if ($result1->num_rows > 0) {
            // output data of each row
            while($row1 = $result1->fetch_assoc()) {
            echo "<tr>
                <td>" . $row1["invoiceitemdescription"] . "</td>
                <td style=\"text-align:center\">" . $row1["invoiceitemquantity"] . "</td>
                <td style=\"text-align:right\">" . $row1["invoiceitemprice"] . "</td>
                <td style=\"text-align:right\">" . $row1["invoiceitemsubtotal"] . "</td>
            </tr>";
            }
            echo "<tr>
                <td colspan=\"3\" style=\"text-align:right; border-bottom:0px;\";>Subtotal</td>
                <td style=\"text-align:right; border-bottom:0px;\">" . $transactionamount . "</td>
            </tr>
            <tr>
                <td colspan=\"2\" style=\" border-bottom:0px;\">&nbsp;</td>
                <td style=\"text-align:right; border-top:2px; border-color:black;\";>GST</td>
                <td style=\"text-align:right; border-top:2px; border-color:black;\">" . $transactiongst . "</td>
            </tr>
            <tr>
                <td colspan=\"3\" style=\"text-align:right; font-weight:bold; border-bottom:0px;\";>Total</td>
                <td style=\"text-align:right; font-weight:bold; border-bottom:0px;\">" . $transactiontotal . "</td>
            </tr>";
            // output the amount paid
            echo "<tr>
                <td colspan=\"2\" style=\" border-bottom:0px;\">&nbsp;</td>
                <td style=\"text-align:right; border-bottom:0px;\";>Paid</td>
                <td style=\"text-align:right; border-bottom:0px;0\">" . $reconciledamount . "</td>
            </tr>
            <tr>
                <td colspan=\"2\" style=\" border-bottom:0px;\">&nbsp;</td>
                <td style=\"text-align:right; border-top:1px solid black;\";>Balance</td>
                <td style=\"text-align:right; font-weight:bold; border-top:1px solid black;\">" . $balanceowing . "</td>
            </tr>";
        }
        ?>
        </tbody>
        </table>
        </td>
    </tr>
    </table>

<!-- Footer -->



<div class="footer">
    <table style="width:780px; margin-left:auto; margin-right:auto;">
        <tr>
            <td style="border:0px;">
                <p>Pay by internet <?php echo $bankaccountnumber?></p>
                <p>Please pay by: <?php echo $invoiceduedate?></p>
            </td>
            <td style="border:0px;">
                <img src="<?php echo $logoimagefilepath?>" style="width:150px; float:right;">
                <p></p>
            </td>
        </tr>
    </table>
</div>

</body>

</html>