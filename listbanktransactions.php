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

$searchstring = "";
$searchstringErr = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (empty($_POST["searchstring"])) {
    $searchstring = "";
  } else {
    $searchstring = test_input($_POST["searchstring"]);
    //check if the field only contains letters dash or white space
    if (!preg_match('/^[a-zA-Z0-9āēīōūĀĒĪŌŪ .\/-]*$/', $searchstring)) {
        $searchstringErr = "Prohibited characters used in search sting";
        //$searchstring = "";
        //$searchstring = mb_convert_encoding($searchstring, 'UTF-8');
    } //else {
    //  $searchstring = "%".$searchstring."%";
    //}
  }
}

function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

$sqluser = "SELECT * FROM accounts WHERE id = $accountid";
$resultuser = $con->query($sqluser);

while($rowuser = $resultuser->fetch_assoc()) {
    $recordownerid = $rowuser["companyID"]; 
}
?>

<?=template_header('List Transactions')?>

<div class="page-title">
	<div class="icon">
		<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z"/></svg>
	</div>	
	<div class="wrap">
		<h2>Account Transactions</h2>
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

?>

<div class="row">
    <div class="col-sm-1">
        <?php echo "<img src=\"" . $connectionlogo . "\" style=\"width:73px;padding-bottom:20px;\">"?>
    </div>
    <div class="col-sm-2">
        <?php echo "<strong>" . $holder . "</strong><br>" . $formattedaccount . "<br><span style=\"color:#AAA;font-weight:500;\">" . $name . "</span>"?>
    </div> 
    <div class="col-sm-3" style="text-align:right; padding-top:5px;"><span class="error"><span class="text-danger"><?php echo $searchstringErr;?></span></div>
    <div class="col-sm-6" style="text-align:right;">
      <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"].'?account='.$QPaccount);?>" style="display:flex;">
        <input class="form-control" id="searchstring" type="text" name="searchstring" value="<?php echo $searchstring;?>" placeholder="Search in Date, Type, Description, Merchant or Amount" style="border-radius:3px 0px 0px 3px;">
        <button class="btn btn-primary" style="border-radius:0px;" type="submit">GO</button>
      </form>
    </div>
  
  <!--
    <div class="col-sm-4" style="text-align:right;">
        <input class="form-control" id="myInput" type="text" placeholder="Search transactions">
    </div>-->
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



<table class="table table-striped">
  <thead>
    <tr>
      <th><?php if ($QPorder == 'date, idtransactions') {
        echo "<a href=\"?account=".$QPaccount."&order=date DESC, idtransactions DESC\" style=\"text-decoration:none; color:inherit;\">";
      } else {
        echo "<a href=\"?account=".$QPaccount."&order=date, idtransactions\" style=\"text-decoration:none; color:inherit;\">";
      }?>Date</a></th>
      <th><?php if ($QPorder == 'type') {
        echo "<a href=\"?account=".$QPaccount."&order=type DESC\" style=\"text-decoration:none; color:inherit;\">";
      } else {
        echo "<a href=\"?account=".$QPaccount."&order=type\" style=\"text-decoration:none; color:inherit;\">";
      }?>Type</a></th>
      <th><?php if ($QPorder == 'description') {
        echo "<a href=\"?account=".$QPaccount."&order=description DESC\" style=\"text-decoration:none; color:inherit;\">";
      } else {
        echo "<a href=\"?account=".$QPaccount."&order=description\" style=\"text-decoration:none; color:inherit;\">";
      }?>Description</a></th>
      <th><?php if ($QPorder == 'merchant_name') {
        echo "<a href=\"?account=".$QPaccount."&order=merchant_name DESC\" style=\"text-decoration:none; color:inherit;\">";
      } else {
        echo "<a href=\"?account=".$QPaccount."&order=merchant_name\" style=\"text-decoration:none; color:inherit;\">";
      }?>Merchant</a></th>
      <th style="text-align:right;"><?php if ($QPorder == 'amount') {
        echo "<a href=\"?account=".$QPaccount."&order=amount DESC\" style=\"text-decoration:none; color:inherit;\">";
      } else {
        echo "<a href=\"?account=".$QPaccount."&order=amount\" style=\"text-decoration:none; color:inherit;\">";
      }?>Amount</a></th>
      <th style="text-align:center;"><?php if ($QPorder == 'idreconciliation DESC, date') {
        echo "<a href=\"?account=".$QPaccount."&order=idreconciliation, date\" style=\"text-decoration:none; color:inherit;\">";
      } else {
        echo "<a href=\"?account=".$QPaccount."&order=idreconciliation DESC, date\" style=\"text-decoration:none; color:inherit;\">";
      }?>Status</a></th>
    </tr>
  </thead>
  <tbody id="myTable">

<?php


// Pagination variables
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$results_per_page = 20; // Number of results per page
$offset = ($page - 1) * $results_per_page;
// Get total number of records
$total_query = "SELECT COUNT(*) AS total FROM banktransactions WHERE _account = '$QPaccount' AND recordownerid IN ($accessto) and (
date LIKE '%$searchstring%' OR
description LIKE '%$searchstring%' OR
amount LIKE '%$searchstring%' OR
type LIKE '%$searchstring%' OR
merchant_name LIKE '%$searchstring%')";
$resultCount = $con->query($total_query);
$rowCount = $resultCount->fetch_assoc();
$total_results = $rowCount['total'];
// Calculate total number of pages
$total_pages = ceil($total_results / $results_per_page);

$sql = "SELECT * FROM banktransactions_view WHERE _account = '$QPaccount' and recordOwnerID IN ($accessto) and (
date LIKE '%$searchstring%' OR
description LIKE '%$searchstring%' OR
amount LIKE '%$searchstring%' OR
type LIKE '%$searchstring%' OR
merchant_name LIKE '%$searchstring%') 
ORDER BY $QPorder LIMIT $results_per_page OFFSET $offset";
$result = $con->query($sql);

    
if ($result->num_rows > 0) {
  // output data of each row
  while($row = $result->fetch_assoc()) {
    echo "<tr>
      <td>" . date_format(date_create($row["date"]),"d M Y") . "</a></td>
      <td>" . $row["type"]. "</td>
      <td>" . $row["description"] . "</td>
      <td>" . $row["merchant_name"]. "</td>
      <td style=\"text-align:right;\">" . $row["amount"] . "</td>";
      if ($row["sumreconcile"] == $row["amount"]) {
        echo "<td style=\"text-align:center; color:#00BB00; text-decoration:none;\">Reconciled</td>";
      } else {
        echo "<td style=\"text-align:center;;\"><a href=\"bankrec.php?account=".$QPaccount."\" style=\"color:orange; text-decoration:none;\">Unreconciled</a></td>";
      }

    echo "</tr>";
  }
}
  
    echo "</tbody></table>";

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