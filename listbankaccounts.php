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

<?=template_header('List Bank Accounts')?>

<div class="page-title">
	<div class="icon">
		<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z"/></svg>
	</div>	
	<div class="wrap">
		<h2>Bank Accounts</h2>
	</div>
</div>

<div class="block">


<div class="row">
    <div class="col-sm-8">
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



<table class="table">
    <thead>
        <tr>
            <th>Bank</th>
            <th>Name</th>
            <th>Account Number</th>
            <th>Holder</th>
            <th>Status</th>
            <th style="width:10%;"></th>
        </tr>
    </thead>
    <tbody id="myTable">

<?php
    $sql = "SELECT * FROM bankaccounts WHERE recordOwnerID IN ($accessto) ORDER BY name";
    $result = $con->query($sql);

    
if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
      echo "<tr>
              <td><img src=\"" . $row["connection_logo"] . "\" style=\"width:70px;\"></a></td>
              <td style=\"padding-top:25px;\">" . $row["name"] . "</td>
              <td style=\"padding-top:25px;\">" . $row["formatted_account"] . "</td>
              <td style=\"padding-top:25px;\">" . $row["holder"]. "</td>
              <td style=\"padding-top:25px;\">" . $row["status"]. "</td>
              <td style=\"padding-top:25px;\"><a href=\"/listbanktransactions.php?account=" . $row["_id"] . "\">View transactions</a></td>";
      echo "    </tr>";
    }
} else {
    echo "0 results";
}
  
    echo "</tbody></table>";
    
    $con->close();

?>

<div class="row">
  <div class="col-sm-2" style="padding-top:20px;"><a href="update_bank_accounts.php" class="btn btn-primary">Refresh</a></div>
  <div class="col-sm-10" style="padding-top:20px; text-align:right"><a href="addbankaccount.php" class="btn btn-primary">Add/Remove Bank Accounts</a></div>
</div>




<?=template_footer()?>