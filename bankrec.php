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

$sqluser = "SELECT * FROM accounts WHERE id = $accountid";
$resultuser = $con->query($sqluser);

while($rowuser = $resultuser->fetch_assoc()) {
    $recordownerid = $rowuser["companyID"]; 
}
?>

<?=template_header('Reconcile Transactions')?>

<div class="page-title">
	<div class="icon">
		<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z"/></svg>
	</div>	
	<div class="wrap">
		<h2>Reconcile Transactions</h2>
	</div>
</div>

<div class="block">

<?php

$Q = explode("/", $_SERVER['QUERY_STRING']);
parse_str($Q[0],$QueryParameters);
$QPaccount = $QueryParameters['account'];
if (!empty($QueryParameters['order'])) {
  $QPorder = $QueryParameters['order'];
} else {
  $QPorder = "date DESC, idtransactions DESC";
}

$sql1 = "SELECT * FROM bankaccounts WHERE _id = '$QPaccount'";
$result1 = $con->query($sql1);

while($row1 = $result1->fetch_assoc()) {
    $connectionlogo = $row1["connection_logo"];
    $holder = $row1["holder"];
    $name = $row1["name"]; 
    $formattedaccount = $row1["formatted_account"]; 
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
  //Create a reconciliation record
  if (!empty($_POST["banktransactionid"])) {
    $banktransactionid = $_POST["banktransactionid"];
  }
  if (!empty($_POST["invoiceid"])) {
    $invoiceid = $_POST["invoiceid"];
  }
  if (!empty($_POST["reconcileamount"])) {
    $reconcileamount = $_POST["reconcileamount"];
  }
  if (!empty($_POST["transactiontotal"])) {
    $transactiontotal = $_POST["transactiontotal"];
  }
  if (!empty($_POST["account"])) {
    $QPaccount = $_POST["account"];
  }
  if (!empty($_POST["invoicepaiddate"])) {
    $invoicepaiddate = $_POST["invoicepaiddate"];
  }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" and $banktransactionid <> NULL and $invoiceid <> NULL and $reconcileamount <> NULL and $transactiontotal <> NULL and $invoicepaiddate <> NULL) {
  //prepare and bind
  if ($reconcileamount == $transactiontotal) {
    $ispartpayment = 0;
  } else {
    $ispartpayment = 1;
  }
  // Check to see if there is already a reconciliation record with matching bank transaction and transaction ID's
  $sqlcheck = "SELECT * FROM reconciliations WHERE bankTransactionID = $banktransactionid and invoiceID = $invoiceid";
  $resultcheck = $con->query($sqlcheck);
  if ($resultcheck->num_rows == 0) { // If no reconciliation record exists
    // Create a reconciliation record
    $stmt = $con->prepare("INSERT INTO reconciliations (bankTransactionID, invoiceID, reconcileamount, isPartPayment) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iidi", $banktransactionid, $invoiceid, $reconcileamount, $ispartpayment);
    if ($stmt->execute()) {
      // Update the transaction record to reconciled
      $sqlrec = "SELECT * FROM transactionsreconciled_view WHERE idtransaction = $invoiceid";
      $resultrec = $con->query($sqlrec);
      if ($resultrec->num_rows == 1) {
        while($rowrec = $resultrec->fetch_assoc()) {
          if ($rowrec["transactiontotal"] == $rowrec["reconciledamount"]) {
            $stmt = $con->prepare("UPDATE transactions SET isReconciled = 1, invoiceStatusID = 11, invoicePaidDate = ? WHERE idtransaction = ?");
            $stmt->bind_param("si", $invoicepaiddate, $invoiceid);
            $stmt->execute();
            $stmt->close();
          }
        }
      } else {
        echo "no matching transaction record!!!" . $invoiceid;
      }
    }
  }
}
  

?>

<div class="row">
    <div class="col-sm-1">
        <?php echo "<img src=\"" . $connectionlogo . "\" style=\"width:73px;padding-bottom:20px;\">"?>
    </div>
    <div class="col-sm-7">
        <?php echo "<strong>" . $holder . "</strong><br>" . $formattedaccount . "<br><span style=\"color:#AAA;font-weight:500;\">" . $name . "</span>"?>
    </div> 
    <div class="col-sm-4" style="text-align:right;">
        <input class="form-control" id="myInput" type="text" placeholder="Search transactions">
    </div>
</div>

<script>
$(document).ready(function(){
  $("#myInput").on("keyup", function() {
	var value = $(this).val().toLowerCase();
	$("#myTable tr").filter(function() {
  	$(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
	});
  });
});
</script>




<div class="row" style="background-color:#FFF;">

<div class="row" style="margin:20px;">
  <div class="col-sm-6">Bank statement Line</div>
  <div class="col-sm-6">Match with an invoice or bill</div>
</div>

<?php


// Pagination variables
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$results_per_page = 20; // Number of results per page
$offset = ($page - 1) * $results_per_page;
// Get total number of records
$total_query = "SELECT COUNT(*) AS total FROM banktransactions_view WHERE _account = '$QPaccount' AND recordownerid IN ($accessto) AND (ABS(amount) <> ABS(sumreconcile) OR sumreconcile IS NULL)";
$resultCount = $con->query($total_query);
$rowCount = $resultCount->fetch_assoc();
$total_results = $rowCount['total'];
// Calculate total number of pages
$total_pages = ceil($total_results / $results_per_page);

$sql = "SELECT * FROM banktransactions_view WHERE _account = '$QPaccount' and recordOwnerID IN ($accessto) AND sumreconcile IS NULL ORDER BY $QPorder LIMIT $results_per_page OFFSET $offset";
// use this search to display all records: $sql = "SELECT * FROM banktransactions_view WHERE _account = '$QPaccount' and recordOwnerID IN ($accessto) and date = '2025-06-06 00:00:00' ORDER BY $QPorder LIMIT $results_per_page OFFSET $offset";
$result = $con->query($sql);

    
if ($result->num_rows > 0) {
  // output data of each bank transaction row
  while($row = $result->fetch_assoc()) {
    if ($row["amount"] > 0) {
      $background = "#EEE";
    } else {
      $background = "#EEC";
    }
    ?>
    <div class="row" style="margin:20px;">
    <div class="col-sm-3" style="background-color:<?php echo $background;?>; padding:20px">
      <?php echo date_format(date_create($row["date"]),"d M Y")?> <br>
      <?php echo $row["type"] . "<br>"
      . $row["description"] . "<br>";
      if ((isset($row["particulars"]) && $row["particulars"] != "") or (isset($row["code"]) && $row["code"] != "") or (isset($row["reference"]) && $row["reference"] != "")) {
        echo $row["particulars"] . " | " . $row["code"] . " | " . $row["reference"] . "<br>";
      }
      echo $row["merchant_name"] ?>
    </div>
    <div class="col-sm-1" style="background-color:<?php echo $background;?>; padding:20px; text-align:right;"><span class="text-secondary">Spent</span><br>
      <?php
      if ($row["amount"] < 0) {
        echo "<span style=\"font-weight:400; font-size:22px;\">$" . number_format($row["amount"] * -1, 2, '.', ',') . "</span>";
      }
      ?>
    </div>
    <div class="col-sm-1" style="background-color:<?php echo $background;?>; padding:20px; text-align:right;"><span class="text-secondary">Received</span><br>
    <?php
    if ($row["amount"] > 0) {
      echo "<span style=\"font-weight:400; font-size:22px;\">$" . number_format($row["amount"],2,'.',',') . "</span>";
    }
    ?>
    </div>
    <?php
    // List the transactions
    $suggestionspan = 0.1;
    $particulars = $row["particulars"];
    $code = $row["code"];
    $reference = $row["reference"];
    $invoicepaiddate =  substr($row["date"], 0, 10);
    if ($row["amount"] < 0) {
      $rowamount = $row["amount"] * -1;
      $lowamount = $row["amount"] * (1-$suggestionspan) * -1;
      $highamount = $row["amount"] * (1+$suggestionspan) * -1;
      $sqlsuggestinvoice = "SELECT * FROM transactions_view WHERE transactiontypeid = 2 and invoicestatusid in (2,4) and isreconciled = 0 and balanceowing BETWEEN $lowamount and $highamount AND recordownerid IN ($accessto) ORDER BY invoiceduedate desc LIMIT 30";
      $nomatchmessage = "No matching opex bills";
      $sqltransacion = "SELECT * FROM transactions_view WHERE transactiontypeid = 2 and invoicestatusid = 4 and idtransaction = '$code' AND recordOwnerID IN ($accessto) LIMIT 1";
    } else {
      $rowamount = $row["amount"];
      $lowamount = $row["amount"] * (1-$suggestionspan);
      $highamount = $row["amount"] * (1+$suggestionspan);
      $sqlsuggestinvoice = "SELECT * FROM transactions_view WHERE transactiontypeid = 1 and invoicestatusid in (2,8) and (((balanceowing is NULL and transactiontotal BETWEEN $lowamount and $highamount) or balanceowing BETWEEN $lowamount and $highamount) OR (invoicenumber = '$reference' OR invoicenumber = '$code' OR invoicenumber = '$particulars')) AND recordownerid IN ($accessto)LIMIT 6";
      $nomatchmessage = "No matching invoices";
      $sqltransacion = "SELECT * FROM transactions_view WHERE invoicestatusid in (2,8) and transactiontotal = $rowamount AND recordownerid IN ($accessto) LIMIT 1";
    }

      $resultsuggestinvoice = $con->query($sqlsuggestinvoice);

      $resulttransaction = $con->query($sqltransacion);
      if ($resulttransaction->num_rows > 0) {
        ?>
        <div class="col-sm-1" style="padding-top:30px">
        <?php while($rowtransaction = $resulttransaction->fetch_assoc()) {
          ?>
        <form class="form form-medium" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"].'?account='.$QPaccount);?>">
          <input hidden id="banktransactionid" type="text" name="banktransactionid" value="<?php echo $row["idtransactions"]?>">
          <input hidden id="invoiceid" type="text" name="invoiceid" value="<?php echo $rowtransaction["idtransaction"]?>">
          <input hidden id="reconcileamount" type="text" name="reconcileamount" value="<?php echo $row["amount"]*-1?>">
          <input hidden id="transactiontotal" type="text" name="transactiontotal" value="<?php echo $row["amount"]*-1?>">
          <input hidden id="account" type="text" name="account" value="<?php echo $QPaccount?>">
          <input hidden id="invoicepaiddate" type="text" name="invoicepaiddate" value="<?php echo date_format(date_create($invoicepaiddate),"Y-m-d")?>">
          <input type="submit" value="Reconcile" class="btn btn-primary" style="width:100px">
        </form>
        </div>
        <div class="col-sm-3" style="background-color:#AEA; padding:20px">
          <?php echo date_format(date_create($rowtransaction["transactiondate"]),"d M Y") . "<br>
          Invoice: " . $rowtransaction["invoicenumber"] . "<br>"
          . $rowtransaction["companyname"] . "<br>"
          . $rowtransaction["transactioncategoryname"] ?>
        </div>
        <div class="col-sm-1" style="background-color:#AEA; padding:20px; text-align:right;"><span class="text-secondary">&nbsp;</span><br>
          <?php
          if ($row["amount"] < 0) {
            echo "<span style=\"font-weight:400; font-size:22px;\">$" . number_format($rowtransaction["transactiontotal"], 2, '.', ',') . "</span>";
          }
          ?>
        </div>
        <div class="col-sm-1" style="background-color:#AEA; padding:20px; text-align:right;"><span class="text-secondary">&nbsp;</span><br>
          <?php
          if ($row["amount"] > 0) {
            echo "<span style=\"font-weight:400; font-size:22px;\">$" . number_format($rowtransaction["transactiontotal"],2,'.',',') . "</span>";
          }
          ?>
        </div>
        <?php
        }
      } else { 
        // No transaction record with a matching transactionID
        // Search for suggested matches based on similar $ amount (+-)
        $resultsuggestinvoice = $con->query($sqlsuggestinvoice);
        if ($resultsuggestinvoice->num_rows > 0) {
          ?>
          <div class="col-sm-1" style="padding:20px"></div>
          </form>
          <div class="col-sm-5" style="background-color:<?php echo $background;?>; padding:20px">Possible matches:<br>
          <table style="width:100%; margin-top:10px;">
            <?php while($rowsuggest = $resultsuggestinvoice->fetch_assoc()) {
              if ($rowsuggest["transactiontypeid"] == 1) { //OPEX bill

              } else { //Invoice

              }
              if ($rowsuggest["balanceowing"] == NULL or $rowsuggest["balanceowing"] == 0) {
                $balanceowing = 0;
                $diplayoutstanding = $rowsuggest["transactiontotal"];
                $displaycolor = "#000";
                $displaytotal = "";
                $displaybalancetext = "";
              } else {
                $balanceowing = $rowsuggest["balanceowing"];
                $diplayoutstanding = $rowsuggest["balanceowing"];
                $displaycolor = "#FF8333";
                $displaytotal = "<br>$" . $rowsuggest["transactiontotal"];
                $displaybalancetext = "<br>(balance)";
              }
              echo "<tr style=\"border-bottom:1px solid #ddd; border-top:1px solid #ddd;\">
              <td style=\"width:20%;\">" . date_format(date_create($rowsuggest["transactiondate"]),"d-m-Y") . "<br>"
              . $rowsuggest["invoicenumber"] . $displaytotal . "</td>
              <td style=\"width:20%;\">" . $rowsuggest["companyname"] . "<br>
              <td style=\"width:20%; text-align:center;\">" . $rowsuggest["transactioncategoryname"] . "</td>
              <td style=\"width:20%; text-align:right;\">" ."<span style=\"font-weight:400; font-size:22px; color:" . $displaycolor . ";\">$" . number_format($diplayoutstanding,2,'.',',') . "</span>" . $displaybalancetext . "</td>
              <td style=\"display:flex; justify-content: right; align-items: center; height: 80px;\">";
              ?>
              <form style="margin:0px;" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"].'?account='.$QPaccount);?>">
                <input hidden id="banktransactionid" type="text" name="banktransactionid" value="<?php echo $row["idtransactions"]?>">
                <input hidden id="invoiceid" type="text" name="invoiceid" value="<?php echo $rowsuggest["idtransaction"]?>">
                <input hidden id="reconcileamount" type="text" name="reconcileamount" value="<?php echo $row["amount"]?>">
                <input hidden id="transactiontotal" type="text" name="transactiontotal" value="<?php echo $rowsuggest["transactiontotal"]?>">
                <input hidden id="account" type="text" name="account" value="<?php echo $QPaccount?>">
                <input hidden id="invoicepaiddate" type="text" name="invoicepaiddate" value="<?php echo date_format(date_create($invoicepaiddate),"Y-m-d")?>">
                <input type="submit" value="Apply" class="btn btn-primary btn-sm" style="width:80px">
              </form>
              <?php echo "</td></tr>";
            }?>
            </table>
          </div>
          <?php
        } else {?>
          <div class="col-sm-1" style="padding:20px"></div>
          <div class="col-sm-3" style="background-color:<?php echo $background;?>; padding:20px"><?php echo $nomatchmessage?></div>
          <div class="col-sm-1" style="background-color:<?php echo $background;?>; padding:20px">&nbsp;</div>
          <div class="col-sm-1" style="background-color:<?php echo $background;?>; padding:20px">&nbsp;</div>
          <?php
        }
      }



    ?>
    </div>
    
    <?php
  }
}
?>

</div>
<?php
// Display pagination links
$first_displayed_record_number = $offset + 1;
if ($total_results < $offset + $results_per_page) {
    $last_displayed_record_number = $total_results;
} else {
    $last_displayed_record_number = $offset + $results_per_page;
}

echo
"<div class=\"row\">
  <div class=\"col-sm-2\" style=\"padding-top:5px;\">";
  if ($last_displayed_record_number > 0) {
    echo $first_displayed_record_number." to ".$last_displayed_record_number." of ";
  }
  echo $total_results." items</div>
  <div class=\"col-sm-3 pagination\">";
  for ($x = 1; $x <= $total_pages; $x++) {
    if ($page == $x) {
      echo '<a class="active">' . $x . '</a>';
    } else {
      echo '<a href="?page=' . ($x) . '&account=' . $QPaccount . '&order=' . $QPorder .'">' . ($x) . '</a>';
    }
  }
  echo "</div>
</div>";

    $con->close();
?>


<?=template_footer()?>