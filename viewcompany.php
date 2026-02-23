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

<?=template_header('View Company')?>

<div class="page-title">
	<div class="icon">
		<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z"/></svg>
	</div>	
	<div class="wrap">
		<h2>Company Details</h2>
<!--		<p>Welcome back, <?=htmlspecialchars($_SESSION['account_name'], ENT_QUOTES)?>!</p>  -->
	</div>
</div>

<div class="block">

<?php

$Q = explode("/", $_SERVER['QUERY_STRING']);
parse_str($Q[0],$QueryParameters);
$QPcompanyid = $QueryParameters['companyid'];

?>

<table class="table">

<?php
$sql = "SELECT * FROM companies_view WHERE idcompany = $QPcompanyid and recordOwnerID IN ($accessto)";
$result = $con->query($sql);

$sql2 = "SELECT * FROM companytype";
$result2 = $con->query($sql2);

$sql3 = "SELECT * FROM contacts WHERE companyID = $QPcompanyid and recordOwnerID IN ($accessto) ORDER BY firstName";
$result3 = $con->query($sql3);

if ($result->num_rows > 0) {
  // output data of each row
  while($row = $result->fetch_assoc()) {

    $primarycontactid = $row["primarycontactid"];

    echo "<tr>
            <td style=\"width:25%\">Company Name:</td>
            <td style=\"width:25%\"><h4>" . $row["companyname"] . "</h4></td>
            <td style=\"width:25%\"></td>
            <td style=\"width:25%\"></td>
        </tr>
        <tr>
            <td>Address1:</td><td colspan=\"3\">" . $row["address1"] . "</td>
        </tr>
        <tr>
            <td>Address2:</td><td colspan=\"3\">" . $row["address2"] . "</td>
        </tr>
        <tr>
            <td>Suburb:</td><td colspan=\"3\">" . $row["suburb"] . "</td>
        </tr>
        <tr>
            <td>City:</td><td colspan=\"3\">" . $row["city"] . "</td>
        </tr>
        <tr>
            <td>Post Code:</td><td colspan=\"3\">" . $row["postcode"] . "</td>
        </tr>
        <tr>
            <td>Type:</td><td colspan=\"3\">" . $row["companytype"] . "</td>
        </tr>
        <tr>
            <td>Primary Contact:</td><td colspan=\"3\"><a href=\"viewcontact.php?contactid=" . $primarycontactid . "\">" . $row["firstname"] . " ". $row["lastname"] . "</a></td>
        </tr>";

/*
        //Get the property manager company
        if ($row["propertymanagercompanyid"] == 0) {
            $propertymanagercompanyid = "";
            $propertymanagercompanyname = "";
        } else {
            $propertymanagercompanyid = $row["propertymanagercompanyid"];
            $sql4 = "SELECT idcompany, companyName FROM companies WHERE idcompany = $propertymanagercompanyid";
            $result4 = $con->query($sql4);
            while($row4 = $result4->fetch_assoc()) {
                $propertymanagercompanyname = $row4["companyName"];
            }
        }
       

        
    echo "<tr>
            <td>Property Management Company:</td><td colspan=\"3\">" . $propertymanagercompanyname . "</td>
        </tr>
*/ 


    echo "<tr>
            <td>NZBN:</td><td colspan=\"3\">" . $row["nzbn"] . "</td>
        </tr>
        <tr>
            <td>GST Number:</td><td colspan=\"3\">" . $row["gstnumber"] . "</td>
        </tr>";
  }
} else {
  echo "0 results";
}

echo "</tbody></table>";

if (isset($_SESSION['account_role']) && $_SESSION['account_role'] !== 'View Only') {
    echo
    "<div class=\"row\">
            <div class=\"col-sm-2\" style=\"padding-top:20px; padding-bottom:20px\"><a href=\"editcompany.php?companyid=" . $QPcompanyid . "\" class=\"btn btn-primary\">Edit Company</a></div>
    </div>";
}

echo "<div>
    <h3 style=\"padding:15px 0 15px 0;\">Contacts:</h3>
</div>

<div class=\"container-fluid\" style=\"margin:10px 0 10px 0;\">
    <div class=\"row\" style=\"margin:3px 0 10px 0;\">
    <div class=\"col-sm-3\"><strong>Name</strong></div>
    <div class=\"col-sm-4\"><strong>Email</strong></div>
    <div class=\"col-sm-2\"><strong>Mobile</strong></div>
    <div class=\"col-sm-2\"><strong>Phone</strong></div>
    <div class=\"col-sm-1\">&nbsp;</div>
</div>";
while($row3 = $result3->fetch_assoc()) {
    echo
    "<div class=\"row\" style=\"padding:10px; border-top:solid 1px #ccc;\">
        <div class=\"col-sm-3\">" . $row3["firstName"] . " " . $row3["lastName"] . "</div>
        <div class=\"col-sm-4\">" . $row3["emailAddress"] . "</div>
        <div class=\"col-sm-2\">" . $row3["mobileNumber"] . "</div>
        <div class=\"col-sm-2\">" . $row3["phoneNumber"] . "</div>
        <div class=\"col-sm-1\"><a href=\"viewcontact.php?contactid=" . $row3["idcontacts"] . "\">VIEW</a></div>
    </div>";
  }
echo
"</div>";

        $con->close();
?>


<?=template_footer()?>