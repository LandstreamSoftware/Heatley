<?php
// Include the main.php file
include 'main.php';
// Check if the user is logged in, if not then redirect to login page
check_loggedin($con);
// Template code below

$accountid = $_SESSION['account_id'];

$sql9 = "SELECT * FROM accesscontrol WHERE accountID = $accountid";
$result9 = $con->query($sql9);

$accessto = 0;
$totaloverunder = 0;

if ($result9->num_rows > 0) {
    while($row9 = $result9->fetch_assoc()) {
       $accessto .= "," . $row9["companyID"]; 
    }
}

// Get the user's Company Name
$sql1 = "SELECT companyName FROM accounts_view WHERE id = $accountid";
$result1 = $con->query($sql1);
if ($result1->num_rows > 0) {
  while($row1 = $result1->fetch_assoc()) {
     $mycompanyname = str_replace(",", "", $row1["companyname"]); // No commas
  }
}

//Get the list of bank accounts
$sql2 = "SELECT name, connection_name, formatted_account FROM bankaccounts WHERE recordOwnerID IN ($accessto)";
$result2 = $con->query($sql2);
?>

<?=template_header('OPEX Bills for payment')?>

<script>
    function payInvoice(url) {
    var myWindow = window.open(url,"","width=800,height=900,top=130,left=300");
  }
</script>
<script>
  function toggleCheckboxes(source) {
    let checkboxes = document.querySelectorAll('.invoice-checkbox');
    checkboxes.forEach(checkbox => checkbox.checked = source.checked);
    toggleSubmitButton();
  }

  function toggleSubmitButton() {
    let checkboxes = document.querySelectorAll('.invoice-checkbox');
    let selectAll = document.getElementById('select-all');
    let submitButton = document.getElementById('submit-button');
    
    let isChecked = Array.from(checkboxes).some(checkbox => checkbox.checked);
    submitButton.style.display = isChecked ? 'block' : 'none';
    
    // Ensure "Select All" reflects the state of individual checkboxes
    selectAll.checked = checkboxes.length > 0 && Array.from(checkboxes).every(checkbox => checkbox.checked);

    let bankAccount = document.getElementById('mybankaccount');
    bankAccount.style.display = "block";
  }
</script>


<div class="page-title">
	<div class="icon">
		<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z"/></svg>
	</div>	
	<div class="wrap">
		<h2>Pay Opex Bills</h2>

	</div>
</div>

<div class="block">

<?php
$Q = explode("/", $_SERVER['QUERY_STRING']);
parse_str($Q[0],$QueryParameters);
if (!empty($QueryParameters['order'])) {
  $QPorder = $QueryParameters['order'];
} else {
  $QPorder = "transactiondate DESC";
}

// Pagination variables
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$results_per_page = 20; // Number of results per page
$offset = ($page - 1) * $results_per_page;
// Get total number of records
$total_query = "SELECT COUNT(*) AS total FROM transactions_view WHERE invoicestatusid IN (2,6,7,8,9,10) and transactiontypeid IN (2,3) and recordownerid IN ($accessto)";
$resultCount = $con->query($total_query);
$rowCount = $resultCount->fetch_assoc();
$total_results = $rowCount['total'];
// Calculate total number of pages
$total_pages = ceil($total_results / $results_per_page);

$sql3 = "SELECT * FROM transactions_view WHERE invoicestatusid IN (2,6,7,8,9,10) and transactiontypeid IN (2,3) and recordOwnerID IN ($accessto) ORDER BY $QPorder LIMIT $results_per_page OFFSET $offset";
$result3 = $con->query($sql3);


echo "<div class=\"container-fluid\" style=\"margin:3px 0 0 0;\">

<div class=\"row\">
    <div class=\"col-sm-6\"><h3 style=\"padding:15px 0 15px 0;\">OPEX Bills for payment</h3></div>
    <div class=\"col-sm-6\" style=\"padding:20px 0 0 0; text-align:right;\">
        <input class=\"form-control\" id=\"myInput\" type=\"text\" placeholder=\"Search Invoice Number, Supplier name or Category\">
    </div>
</div>

<div class=\"container-fluid\" style=\"margin:3px 0 0 0;\">";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

  if (isset($_POST['invoice']) && !empty($_POST['invoice'])) {
    date_default_timezone_set('Pacific/Auckland');

    $filename = "bankfiles/" . $accountid . date("Ymd") . ".mts";
    $batchtotalamount = 0;
    $transactioncount = 0;
    $hashtotal = 0;
    $mybankaccountnumber = str_replace("-", "", $_POST['mybankaccount']);
    
    // Open file in write mode
    $file = fopen($filename, "w");
    
    // Add MTS header record
    fputcsv($file, ["1","","","",$mybankaccountnumber,"",date("Ymd"),date("Ymd"),""]);

    // Write selected invoices to CSV
    foreach ($_POST['invoice'] as $invoice) {

      

      // Get the invoice data
      $sql = "SELECT * FROM transactions_view WHERE idtransaction = $invoice";
      $result = $con->query($sql);
      if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
          $filetotal = number_format($row["transactiontotal"],2,'','');

          // Check that the supplier Company record has a bank account number loaded
          if(!isset($row["bankaccountnumber"]) || $row["bankaccountnumber"] == "") {
            echo "No bank account number loaded for " . $row["companyname"] . "<br>";
          } else {

            $banum = $row["bankaccountnumber"];
            $pos1 = strpos($banum,"-",0) + 1; // The first - after bank number
            $str1 = substr($banum, $pos1, 20); // Branch-account-suffix
            //echo "Bank Account: " . $banum . "<br>";
            $pos2 = strpos($str1,"-", 0); // First - after branch
            //echo $pos2 . "<br>";
            $branch = number_format(substr($str1, 0, $pos2),0,"","") * 10000000;
            //echo "Branch: " . $branch . "<br>";
            $str2 = substr($str1, $pos2 + 1, 20);
            $pos3 = strpos($str2, "-", 0);
            $account = number_format(substr($str2, $pos3 - 7, 7),0,"","");
            //echo "Account" . $account . "<br>";

            $bankaccountnumber = str_replace("-", "", $row["bankaccountnumber"]);

            $filecompanyname = str_replace(",", "", substr($row["companyname"], 0, 20)); // Max 20 characters, no commas
            // Your statement details
            $origparticulars = $filecompanyname;
            $origanalcode = $row["idtransaction"];
            $origreference = substr($row["invoicenumber"], 0, 12);
            // Their statement details
            $otherparticulars = $mycompanyname;
            switch ($row["transactiontypeid"]) {
              case 2: // Opex bill
                $otheranalcode = "INV";
              break;
              case 3: // Owner payment
                $otheranalcode = "PAYMENT";
              break;
            }
            
            $otherreference = substr($row["invoicenumber"], 0, 12);
          }
        }
      }
      
      // Domestic format
      //fputcsv($file, [$filetotal, $bankaccountnumber, $filecompanyname, $origreference, $origanalcode, $origparticulars, $otherreference, $otheranalcode, $otherparticulars]);
      
      // Domestic extended format
      fputcsv($file, ["2", $bankaccountnumber, "50", $filetotal, $filecompanyname, $otherreference, $otheranalcode, "", $otherparticulars, $mycompanyname, $origanalcode, $origreference, $origparticulars,]);
      
      $batchtotalamount += $filetotal;
      $transactioncount += 1;
      $branchandaccount = $branch + $account;
      $hashtotal += $branchandaccount;
    }
    // Add Control record
    fputcsv($file, ["3",$batchtotalamount,$transactioncount,$hashtotal]);

    fclose($file);


    echo '<div class=\"row\">
            <div class=\"col-sm-4\" style="padding-bottom:20px;">Bank file created successfully!</div>
        </div>';
          if (file_exists($filename)) {
            echo '<div class=\"row\">
            <div class=\"col-sm-4\" style="padding-bottom:60px;"><a class="btn btn-primary" href="/' . $filename . '" download="' . date("Ymd") . '.mts">Download File</a></div>
            </div>';
          }

      echo "<div class=\"row\">
          <div class=\"col-sm-2\"><a href=\"payopexinvoices.php\" class=\"btn btn-primary\">Back to Unpaid Invoices</a></div>
      </div>
      <div class=\"row\">";
    exit();
  } else {
      echo "No invoices selected.";
  }

} else {

?>
<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
<table class="table">
  <thead>
    <tr>
      <th><input type="checkbox" id="select-all" onclick="toggleCheckboxes(this)"></th>
      <th><?php if ($QPorder == 'transactiondate') {
        echo "<a href=\"?order=transactiondate DESC\" style=\"text-decoration:none; color:inherit;\">";
      } else {
        echo "<a href=\"?order=transactiondate\" style=\"text-decoration:none; color:inherit;\">";
      }?>Date</a></th>
      <th><?php if ($QPorder == 'invoicenumber') {
        echo "<a href=\"?order=invoicenumber DESC\" style=\"text-decoration:none; color:inherit;\">";
      } else {
        echo "<a href=\"?order=invoicenumber\" style=\"text-decoration:none; color:inherit;\">";
      }?>Invoice Number</a></th>
      <th><?php if ($QPorder == 'companyname, transactiondate DESC') {
        echo "<a href=\"?order=companyname DESC, transactiondate DESC\" style=\"text-decoration:none; color:inherit;\">";
      } else {
        echo "<a href=\"?order=companyname, transactiondate DESC\" style=\"text-decoration:none; color:inherit;\">";
      }?>Supplier</a></th>
      <th style="text-align:left;"><?php if ($QPorder == 'transactioncategoryname, transactiondate DESC') {
        echo "<a href=\"?order=transactioncategoryname DESC, transactiondate DESC\" style=\"text-decoration:none; color:inherit;\">";
      } else {
        echo "<a href=\"?order=transactioncategoryname, transactiondate DESC\" style=\"text-decoration:none; color:inherit;\">";
      }?>Category</a></th>
      <th style="text-align:right; padding-right:60px;">
      <?php if ($QPorder == 'transactionamount') {
        echo "<a href=\"?order=transactionamount DESC\" style=\"text-decoration:none; color:inherit;\">";
      } else {
        echo "<a href=\"?order=transactionamount\" style=\"text-decoration:none; color:inherit;\">";
      }?>
      Cost</a></th>
      <th style="text-align:right; padding-right:60px;"><?php if ($QPorder == 'transactiontotal') {
        echo "<a href=\"?order=transactiontotal DESC\" style=\"text-decoration:none; color:inherit;\">";
      } else {
        echo "<a href=\"?order=transactiontotal\" style=\"text-decoration:none; color:inherit;\">";
      }?>Total (incl. GST)</a></th>
      <th style="text-align:center;"><?php if ($QPorder == 'invoicestatusid') {
        echo "<a href=\"?order=invoicestatusid DESC\" style=\"text-decoration:none; color:inherit;\">";
      } else {
        echo "<a href=\"?order=invoicestatusid\" style=\"text-decoration:none; color:inherit;\">";
      }?>Status</a></th>
      <th style="text-align:center;"><?php if ($QPorder == 'unitname, transactiondate DESC') {
        echo "<a href=\"?order=unitname DESC, transactiondate DESC\" style=\"text-decoration:none; color:inherit;\">";
      } else {
        echo "<a href=\"?order=unitname, transactiondate DESC\" style=\"text-decoration:none; color:inherit;\">";
      }?>Premises</a></th>
    </tr>
  </thead>
  <tbody id="myTable">

<?php
while($row3 = $result3->fetch_assoc()) {
    if(empty($row3["invoicepaiddate"])){
        $invoicestatus = "Pay Now";
      } else {
        $invoicestatus = "Paid";
      }
    echo "<tr>
      <td style=\"padding-top:12px;\"><input type=\"checkbox\" class=\"invoice-checkbox\" name=\"invoice[]\" value=\"" . $row3["idtransaction"] . "\" onclick=\"toggleSubmitButton()\"></td>
      <td>" . date_format(date_create($row3["transactiondate"]),"j F Y") . "
      <input type=\"text\" name=\"filedate\" value=\"" . $row3["transactiondate"] . "\" hidden>
      </td>
      <td><a href=\"editopexinvoice.php?id=" . $row3["idtransaction"] . "\">" . $row3["invoicenumber"] . "</a></td>
      <td>" . $row3["companyname"] . "</td>
      <td style=\"text-align:left;\">" . $row3["transactioncategoryname"] . "</td>
      <td style=\"text-align:right; padding-right:60px;\">$" . number_format($row3["transactionamount"],2) . "</td>
      <td style=\"text-align:right; padding-right:60px;\">$" . number_format($row3["transactiontotal"],2) . "
      <input type=\"text\" name=\"filetotal\" value=\"" . number_format($row3["transactiontotal"],2) . "\" hidden>
      </td>";
      switch ($row3["invoicestatusid"]) {
        case '7': //ERROR
        case '8':
        case '9':
        case '10':
          echo "<td style=\"text-align:center;\"><a href=payopexinvoice.php?invoiceid=". $row3["idtransaction"] . " style=\"text-decoration:none; color:red;\">".$row3["invoicestatus"]."</a></td>";
          break;
        case '6': //Processing
          echo "<td style=\"text-align:center;\">".$row3["invoicestatus"]."</td>";
          break;
        //case '4': //Paid
          //echo "<td style=\"text-align:center;\">".$row3["invoicestatus"]."</td>";
          //break;
        case '2': //Active
          echo "<td style=\"text-align:center;\"><a href=payopexinvoice.php?invoiceid=". $row3["idtransaction"] . ">Pay Now</a></td>";
          break;
      }
      //if ($row3["invoicestatusid"] == 4) {
      //  echo "<td style=\"text-align:center;\"><a href=payopexinvoice.php?invoiceid=". $row3["idtransaction"] . ">Pay Now</a></td>";
      //} else {
      //  echo "<td style=\"text-align:center;\"><a href=payopexinvoice.php?invoiceid=". $row3["idtransaction"] . ">".$row3["invoicestatus"]."</a></td>";
      //}
      
      if (is_null($row3["premisesid"])) {
        echo "<td style=\"text-align:center;\"><td>";
      } elseif ($row3["premisesid"] == 0) {
        echo "<td style=\"text-align:center;\">common</td>";
      } else {
        echo "<td style=\"text-align:center;\">" .$row3["unitname"] . "</td>";
      }
    "</tr>";
}

echo "</tbody></table>";

echo '<select class="form-control" id="mybankaccount" name="mybankaccount" style="width:30%; display:none;">
  <option value=\"0\"> - Select a Bank Account - </option>';
    while($row2 = $result2->fetch_assoc()) {
      echo "<option value=\"" . $row2["formatted_account"] . "\">". $row2["connection_name"] . " - " . $row2["name"] . " - " . $row2["formatted_account"] . "</option>";
    }
  echo "</select>
  <br>
<input type=\"submit\" id=\"submit-button\" value=\"Export Bank File\" class=\"btn btn-primary\" style=\"display: none;\">
</form>";

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
      echo '<a href="?page=' . ($x) . '&order=' . $QPorder .'">' . ($x) . '</a>';
    }
  }
  echo "</div>
</div>
</div>";

}

$con->close();
?>

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



</div>

<?=template_footer()?>