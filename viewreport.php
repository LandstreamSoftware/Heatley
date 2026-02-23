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

$building = 0;
$searchstring = "";
$searchstringErr = "";
$balance = 0;
$year = "";
$month = "";
$day = "";

$sqlbuilding = "SELECT idbuildings, buildingName FROM buildings WHERE recordownerid IN ($accessto) ORDER BY buildingName";
$resultbuilding = $con->query($sqlbuilding);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (empty($_POST["searchstring"])) {
    $searchstring = "";
  } else {
    $searchstring = test_input($_POST["searchstring"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/^[a-zA-Z-0-9āēīōūĀĒĪŌŪ' .-\/]*$/", $searchstring)) {
        $searchstringErr = "Prohibited characters used in search sting";
        $searchstring = "";
    }
  }
  if (!empty($_POST["month"])) {
    $month = $_POST["month"];
  }
  if (!empty($_POST["year"])) {
    $year = $_POST["year"];
  }
  if (!empty($_POST["building"])) {
    $building = $_POST["building"];
  }
}

function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

$Q = explode("/", $_SERVER['QUERY_STRING']);
parse_str($Q[0],$QueryParameters);
$QPownerid = $QueryParameters['ownerid'];
$sqlowner = "SELECT companyName FROM companies WHERE idcompany = $QPownerid";
$resultowner = $con->query($sqlowner);
while($rowowner = $resultowner->fetch_assoc()) {
  $ownercompanyname = $rowowner["companyName"];
}
if (!empty($QueryParameters['order'])) {
  $QPorder = $QueryParameters['order'];
} else {
  $QPorder = "invoicepaiddatedate DESC";
}
if (!empty($QueryParameters['searchstring'])) {
  $searchstring = $QueryParameters['searchstring'];
}
if (!empty($QueryParameters['month'])) {
  $month = $QueryParameters['month'];
}
if (!empty($QueryParameters['year'])) {
  $year = $QueryParameters['year'];
}
if (!empty($QueryParameters['building'])) {
  $building = $QueryParameters['building'];
}

?>

<?=template_header('Owner Statement')?>

<div class="page-title">
	<div class="icon">
		<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z"/></svg>
	</div>	
	<div class="wrap">
		<h2>Owner Statement</h2>
    <h5><?php echo $ownercompanyname;?></h5>
	</div>
</div>

<div class="block">

<?php
$months = [
    "01" => "January", "02" => "February", "03" => "March", "04" => "April",
    "05" => "May", "06" => "June", "07" => "July", "08" => "August",
    "09" => "September", "10" => "October", "11" => "November", "12" => "December"
];
$yearhigh = date("Y");
$yearlow = $yearhigh - 5;
?>

<div class="row">
  <div class="col-sm-5">
  <form class="form form-medium" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"].'?ownerid='.$QPownerid.'&searchstring='.$searchstring);?>" style="display:flex;">
  <div class="form-group">
    
    <label class="form-label" for="month">Statement Period:</label>
    
    <div class="col-sm-2">
      <select class="form-control" name="month" id="month">
        <?php foreach ($months as $num => $name) : ?>
          <option value="<?= $num; ?>" <?= ($num == $month) ? 'selected' : ''; ?>>
            <?= $name; ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="col-sm2">
      <select class="form-control" name="year" id="year">
        <?php
          for ($x = $yearhigh; $x >= $yearlow; $x--) { ?>
          <option value="<?= $x; ?>" <?= ($x == $year) ? 'selected' : ''; ?>>
            <?= $x; ?>
          </option>
        <?php } ?>
      </select>
    </div>

    <div class="col-sm-4">
      <select class="form-control" name="building" id="building">
      <?php while ($rowbuilding = $resultbuilding->fetch_assoc()) {
        if ($rowbuilding["idbuildings"] == $building) {
          echo "<option value=\"" . $rowbuilding["idbuildings"] . "\" selected>". $rowbuilding["buildingName"] . "</option>";
        } else {
          echo "<option value=\"" . $rowbuilding["idbuildings"] . "\">". $rowbuilding["buildingName"] . "</option>";
        }
      }
      ?> 

      </select>
    </div>
    
    <div class="col-sm-2">
      <input type="submit" value="Refresh" class="btn btn-primary">
    </div>
    </div>
  </form>
  </div>

  <div class="col-sm-6" style="text-align:right; padding-top:8px;">
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"].'?ownerid='.$QPownerid.'&month='.$month.'&year='.$year.'&building='.$building);?>" style="display:flex;">
      <input class="form-control" id="searchstring" type="text" name="searchstring" value="<?php echo $searchstring;?>" placeholder="Search in Name, Company or Email" style="border-radius:3px 0px 0px 3px;">
      <button class="btn btn-primary" style="border-radius:0px;" type="submit">GO</button>
    </form>
  </div>
</div>

<table class="table">
  <thead>
    <tr>
      <th style="width:10%;">Date</th>
      <th>Company Name</th>
      <th>Category</th>
      <th style="text-align:right; width:8%;">Debit</th>
      <th style="text-align:right; width:8%;">Credit</th>
      <th style="text-align:right; width:8%;">GST</th>
      <th style="text-align:right; width:8%;">Total</th>
      <th style="text-align:right; width:8%;">Balance</th>
    </tr>
  </thead>
  <tbody id="myTable"></tbody>

<?php
// Pagination variables
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$results_per_page = 150; // Number of results per page
$offset = ($page - 1) * $results_per_page;
// Get total number of records
$total_query = "SELECT COUNT(*) AS total FROM reportowner_view WHERE recordownerid IN ($accessto) and 
invoicestatusid = 4 AND
buildingid = $building AND
invoicepaiddate LIKE '%$year-$month%' AND (
invoicepaiddate LIKE '%$searchstring%' OR
companyname LIKE '%$searchstring%' OR
transactioncategory LIKE '%$searchstring%' OR
transactionamount LIKE '%$searchstring%' OR
transactiontotal LIKE '%$searchstring%')";
$resultCount = $con->query($total_query);
$rowCount = $resultCount->fetch_assoc();
$total_results = $rowCount['total'];
// Calculate total number of pages
$total_pages = ceil($total_results / $results_per_page);

$sql = "SELECT * FROM reportowner_view WHERE recordownerid IN ($accessto) and
invoicestatusid = 4 AND
buildingid = $building AND
invoicepaiddate LIKE '%$year-$month%' and (
invoicepaiddate LIKE '%$searchstring%' OR
companyname LIKE '%$searchstring%' OR
transactioncategory LIKE '%$searchstring%' OR
transactionamount LIKE '%$searchstring%' OR
transactiontotal LIKE '%$searchstring%')
ORDER BY 
CASE
  WHEN transactiontypeid = 3 then 1
  ELSE 0
END, unitname, invoicepaiddate LIMIT $results_per_page OFFSET $offset";
$result = $con->query($sql);

if ($result->num_rows > 0) {
  // output data of each row
  $unitnumber = $lastunitnumber = "";
  while($row = $result->fetch_assoc()) {
    if ($unitnumber != $row["premisesid"]) {
      //Add a heading row
      if ($row["premisesid"] == 0) {
        echo "<tr class=\"table-active\">
          <td colspan=\"8\" style=\"font-weight:500;\">Commom to all units</td>
        </tr>";
      } else {
        if ($row["transactiontypeid"] == 3) {
          echo "<tr class=\"table-primary\">
            <td colspan=\"8\" style=\"font-weight:500;\">Owner Payments</td>
          </tr>";
        } else {
        echo "<tr class=\"table-active\">
          <td colspan=\"8\" style=\"font-weight:500;\">".$row["unitname"].", ".$row["premisesaddress"]."</td>
        </tr>";
        }
      }
      
    }
    $invoicepaiddate = date_format(date_create($row["invoicepaiddate"]),"d M Y");

    echo "<tr>
      <td>" . $invoicepaiddate . "</a></td>
      <td>" . $row["companyname"]. "</td>
      <td>" . $row["transactioncategoryname"]. "</td>";
      $transactiontypeid = $row["transactiontypeid"];
      switch ($transactiontypeid) {
        case '1': //Tenant invoice
          echo "<td></td><td style=\"text-align:right;\">" . $row["transactionamount"]. "</td>";
        break;
        case '2': //Opex bill
          echo "<td style=\"text-align:right;\">" . $row["transactionamount"]. "</td><td></td>";
        break;
        case '3': //Owner payment
          echo "<td style=\"text-align:right;\">" . $row["transactionamount"]. "</td><td></td>";
        break;
      }
      echo "<td style=\"text-align:right;\">" .number_format($row["transactiongst"],2). "</td>";
      switch ($transactiontypeid) {
        case '1': //Tenant invoice
          $transactiontotal = number_format($row["transactiontotal"],2);
          echo "<td style=\"text-align:right;\"><a href=\"edittransaction.php?id=".$row["transactionid"]."\" style=\"text-decoration:none; color:inherit;\">".$transactiontotal."</a></td>";
          $balance = $balance + $row["transactiontotal"];
          break;
        case '2': //Opex bill
          $transactiontotal = number_format($row["transactiontotal"]*-1,2);
          echo "<td style=\"text-align:right;\"><a href=\"editopexinvoice.php?id=".$row["transactionid"]."\" style=\"text-decoration:none; color:inherit;\">".$transactiontotal."</a></td>";
          $balance = $balance - $row["transactiontotal"];
          break;
        case '3': //Owner payment
          $transactiontotal = number_format($row["transactiontotal"]*-1,2);
          echo "<td style=\"text-align:right;\">".$transactiontotal."</td>";
          $balance = $balance - $row["transactiontotal"];
          break;
      }
      
      echo "<td style=\"text-align:right;\">" . number_format($balance, 2). "</td>";

    $unitnumber = $row["premisesid"];
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
      echo '<a href="?page=' . ($x) . '&order=' . $QPorder .'&ownerid='.$QPownerid.'&searchstring='.$searchstring.'&month='.$month.'&year='.$year.'&building='.$building.'">' . ($x) . '</a>';
    }
  }
  echo "</div>";

  if ($balance > 0) {
    //$ownerpaymentamount = number_format($balance,2);
  ?>
  <div class="col-sm-5" style="text-align:right; padding-top:5px;">
    Make payment to owner:
  </div>  
  <div class="col-sm-1" style="padding-top:5px; text-align:right;">
    $<?php echo number_format($balance,2);?>
  </div>
  <div class="col-sm-1" style="text-align:center;">
    <a class="btn btn-primary" href="payowner.php?ownerid=<?php echo $QPownerid;?>&amount=<?php echo number_format($balance,2,".","");?>&month=<?php echo $month;?>&year=<?php echo $year;?>&building=<?php echo $building?>">Pay Owner</a>
  </div>


  <?php
  }
  ?>
</div>
<?php
        $con->close();
?>


<?=template_footer()?>