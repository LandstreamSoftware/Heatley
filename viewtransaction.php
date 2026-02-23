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
$id = $QueryParameters['id'];
if (!empty($QueryParameters['searchstring'])) {
  $searchstring = $QueryParameters['searchstring'];
} else {
  $searchstring = ""; 
}
if (!empty($QueryParameters['opex'])) {
  $opex = $QueryParameters['opex'];
}
if (!empty($QueryParameters['type'])) {
    $type = $QueryParameters['type'];
  } else {
    // Get the Transaction Type ID
    $sqltransid = "SELECT * FROM transactions_view WHERE idtransaction = $id and recordOwnerID IN ($accessto)";
    $resulttransid = $con->query($sqltransid);
    while($rowtransid = $resulttransid->fetch_assoc()) {
      $type = $rowtransid["transactiontypeid"];
    }
  }

switch ($type) {
    case 1:
      $headingtext = "Invoice Details";
      break;
    case 2:
      $headingtext = "OPEX Bill Details";
      break;
    default:
      $headingtext = "Invoice Details";
      $type = 1;
  }
  template_header($headingtext);
  ?>

<div class="page-title">
	<div class="icon">
		<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z"/></svg>
	</div>	
	<div class="wrap">
		<h2><?php echo $headingtext; ?></h2>
	</div>
</div>

<div class="block">

<table class="table">

<?php
$sql = "SELECT * FROM transactions_view WHERE idtransaction = $id and recordOwnerID IN ($accessto)";
$result = $con->query($sql);


if ($result->num_rows > 0) {
  // output data of each row
  while($row = $result->fetch_assoc()) {
    $transactionamount = $row["transactionamount"];
    $transactiongst = $row["transactiongst"];
    $transactiontotal = $row["transactiontotal"];
    $transactiontypeid = $row["transactiontypeid"];
    $invoicestatusid = $row["invoicestatusid"];
    $isreconciled = $row["isreconciled"];
    if (isset($row["reconciledamount"])) {
      $reconciledamount = ABS($row["reconciledamount"]);
    } else {
      $reconciledamount = 0;
    }
    if (isset($row["balanceowing"])) {
      $balanceowing = $row["balanceowing"];
    } else {
      $balanceowing = $row["transactiontotal"];
    }
    

    if($invoicestatusid == 11) {
      echo "<div class=\"alert alert-success\">
        <span style=\"font-weight:400; font-size:22px;\">Reconciled!</span>
      </div>";
    } else {
      echo "<div class=\"alert alert-warning\">";
        if ($balanceowing == 0) {
          echo "<span style=\"font-weight:400;\">Unreconciled";
        } else {
          echo "<span style=\"font-weight:400; font-size:22px;\">" . $balanceowing . "</span> to pay";
        }
      echo "</div>";
    } 

    echo "<tr>
            <td>Company:</td>
            <td>" . $row["companyname"] . "</td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>Date:</td>
            <td>" . $row["transactiondate"] . "</td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td style=\"width:25%\">Invoice Number:</td>
            <td style=\"width:25%\">" . $row["invoicenumber"] . "</td>
            <td style=\"width:25%\"></td>
            <td style=\"width:25%\"></td>
        </tr>
        <tr>
            <td style=\"width:25%\">Category:</td>
            <td style=\"width:25%\">" . $row["transactioncategoryname"] . "</td>
            <td style=\"width:25%\"></td>
            <td style=\"width:25%\"></td>
        </tr>
        <tr>";
      if ($row["transactiontypeid"] == 2) {
        echo
        "<tr>
            <td style=\"width:25%\">Amount:</td>
            <td style=\"width:25%\">" . $row["transactionamount"] . "</td>
            <td style=\"width:25%\"></td>
            <td style=\"width:25%\"></td>
        </tr>
        <tr>
            <td style=\"width:25%\">GST:</td>
            <td style=\"width:25%\">" . $row["transactiongst"] . "</td>
            <td style=\"width:25%\"></td>
            <td style=\"width:25%\"></td>
        </tr>
        <tr>
            <td style=\"width:25%\">Total:</td>
            <td style=\"width:25%\">" . $row["transactiontotal"] . "</td>
            <td style=\"width:25%\"></td>
            <td style=\"width:25%\"></td>
        </tr>
        <tr>
            <td style=\"width:25%\"></td>
            <td style=\"width:25%\"></td>
            <td style=\"width:25%\">Amount Paid:</td>
            <td style=\"width:25%\">" . $row["reconciledamount"] . "</td>
        </tr>
        <tr>
            <td style=\"width:25%\"></td>
            <td style=\"width:25%\"></td>
            <td style=\"width:25%\">Amount Due:</td>
            <td style=\"width:25%; border-top:1px solid #ddd;\">$balanceowing</td>
        </tr>";
      }
        echo
        "<tr>
            <td>Due Date:</td>
            <td>" . $row["invoiceduedate"] . "</td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>Status:</td>
            <td>" . $row["invoicestatus"] . "</td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>Date Paid:</td>
            <td>" . $row["invoicepaiddate"] . "</td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>Premises:</td>
            <td>" . $row["unitname"] . ", " . $row["buildingname"] . "</td>
            <td></td>
            <td></td>
        </tr>";
  }
} else {
  echo "0 results";
}
echo "</tbody></table>";


// Hide the Invoice Items section for Opex Invoices
if ($transactiontypeid == 1) {


echo "
<div>
    <h3 style=\"padding:15px 0 15px 0;\">Invoice Items:</h3>
</div>";

//Display the invoice items
?>
<table class="table">
    <thead>
        <tr>
            <th style="width:33%;">Description</th>
            <th style="width:10%;">Premises</th>
            <th style="width:5%; text-align:center">Quantity</th>
            <th style="width:10%; text-align:right">Price</th>
            <th style="width:15%; text-align:left; padding-left:40px;">Category</th>
            <th style="width:10%; text-align:right">GST</th>
            <th style="width:10%; text-align:right">Amount</th>
            <th style="width:7%;"></th>
        </tr>
    </thead>
    <tbody id="myTable">

    <?php
    $sql1 = "SELECT * FROM invoiceitems_view WHERE invoiceid = $id and recordownerid IN ($accessto) ORDER BY idinvoiceitem";
    $result1 = $con->query($sql1);

if ($result1->num_rows > 0) {
  // output data of each row
  while($row1 = $result1->fetch_assoc()) {
    echo "<tr>
            <td>" . $row1["invoiceitemdescription"] . "</td>
            <td>" . $row1["unitname"] . "</td>
            <td style=\"text-align:center\">" . $row1["invoiceitemquantity"] . "</td>
            <td style=\"text-align:right\">" . $row1["invoiceitemprice"] . "</td>
            <td style=\"padding-left:40px;\">" . $row1["invoicecategoryname"] . "</td>
            <td style=\"text-align:right\">" . $row1["invoiceitemtax"] . "</td>
            <td style=\"text-align:right\">" . $row1["invoiceitemsubtotal"] . "</td>
            <td style=\"text-align:right\"><a href=\"editinvoiceitem.php?invoiceitemid=". $row1["idinvoiceitem"] ."\">Edit</a></td>
        </tr>";
  }
    echo "<tr>
        <td colspan=\"6\" style=\"text-align:right; border-bottom:0px;\";>Subtotal</td>
        <td style=\"text-align:right; border-bottom:0px;\">" . $transactionamount . "</td>
    </tr>
    <tr>
        <td colspan=\"5\" style=\" border-bottom:0px;\">&nbsp;</td>
        <td style=\"text-align:right; border-top:2px; border-color:black;\";>GST</td>
        <td style=\"text-align:right; border-top:2px; border-color:black;\">" . $transactiongst . "</td>
    </tr>
    <tr>
        <td colspan=\"6\" style=\"text-align:right; font-weight:bold; border-bottom:0px;\";>Total</td>
        <td style=\"text-align:right; font-weight:bold; border-bottom:0px;\">" . $transactiontotal . "</td>
    </tr>
    <tr>
        <td colspan=\"5\" style=\" border-bottom:0px;\">&nbsp;</td>
        <td style=\"text-align:right; border-top:2px; border-color:black;\";>Paid</td>
        <td style=\"text-align:right; border-top:2px; border-color:black;\">" . $reconciledamount . "</td>
    </tr>
    <tr>
        <td colspan=\"6\" style=\"text-align:right; font-weight:bold; border-bottom:0px;\";>Balance</td>
        <td style=\"text-align:right; font-weight:bold; border-bottom:0px;\">" . $balanceowing . "</td>
    </tr>";
} else {
  echo "0 results";
}

echo "</tbody></table>
";




echo
"<div class=\"row\">
        <div class=\"col-sm-2\" style=\"padding-top:20px; padding-bottom:20px\"><a href=\"edittransaction.php?id=" . $id . "\" class=\"btn btn-primary\">Edit Invoice</a></div>
        <div class=\"col-sm-2\" style=\"padding-top:20px; padding-bottom:20px\"><a href=\"listtransactions.php?type=" . $type . "\" class=\"btn btn-primary\">Back to Invoices</a></div>
        <div class=\"col-sm-8\" style=\"padding-top:20px; padding-bottom:20px; text-align:right\"><a href=\"addinvoiceitem.php?invoiceid=". $id . "\" class=\"btn btn-primary\">Add Item</a></div>
</div>";

echo "</div";
} else { // Transaction Type <> 1
  if ($invoicestatusid != 4) {
    echo
    "<div class=\"row\">
        <div class=\"col-sm-2\" style=\"padding-top:20px; padding-bottom:20px\"><a href=\"editopexinvoice.php?id=" . $id . "&opex=" . $opex . "&searchstring=" . $searchstring . "\" class=\"btn btn-primary\">Edit Opex Bill</a></div>
        <div class=\"col-sm-2\" style=\"padding-top:20px; padding-bottom:20px\"><a href=\"listtransactions.php?type=" . $type . "&opex=" . $opex . "\" class=\"btn btn-primary\">Back to Bills</a></div>
    </div>";
  } else {
    echo
    "<div class=\"row\">
        <div class=\"col-sm-2\" style=\"padding-top:20px; padding-bottom:20px\"><a href=\"editopexinvoice.php?id=" . $id . "&opex=" . $opex . "&searchstring=" . $searchstring . "\" class=\"btn btn-primary\">Edit Opex Bill</a></div>
        <div class=\"col-sm-2\" style=\"padding-top:20px; padding-bottom:20px\"><a href=\"listtransactions.php?type=" . $type . "&opex=" . $opex . "\" class=\"btn btn-primary\">Back to Bills</a></div>
    </div>";
  }
}

        $con->close();
?>


<?=template_footer()?>