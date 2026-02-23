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

<?=template_header('View Renewal')?>

<div class="page-title">
	<div class="icon">
		<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z"/></svg>
	</div>	
	<div class="wrap">
		<h2>Renewal Details</h2>
<!--		<p>Welcome back, <?=htmlspecialchars($_SESSION['account_name'], ENT_QUOTES)?>!</p>  -->
	</div>
</div>

<div class="block">

<?php
$Q = explode("/", $_SERVER['QUERY_STRING']);
parse_str($Q[0],$QueryParameters);
$QPrenewalid = $QueryParameters['renewalid'];

?>

<table class="table">

<?php
$sql = "SELECT * FROM renewals_view WHERE idrenewals = $QPrenewalid and recordOwnerID IN ($accessto)";
$result = $con->query($sql);

//Find the current OPEX record
$sql1 = "SELECT * FROM notifications_find_opexid_view WHERE idrenewals = $QPrenewalid and opexstatusid = 2 and recordOwnerID IN ($accessto) ORDER BY opexdate DESC LIMIT 1";
$result1 = $con->query($sql1);
while($row1 = $result1->fetch_assoc()) {
    $opexid = $row1["idopex"];
}

if ($result->num_rows > 0) {
  // output data of each row
  while($row = $result->fetch_assoc()) {
    $rentpremises = $row["rentpremises"];
    $isrightofrenewal = $row["isrightofrenewal"];
    $leaseid = $row["leaseid"];
    
    echo "<tr>
            <td colspan=\"4\"><h4>" . $row["tenantname"] . "</h4></td>
        </tr>
        <tr>
            <td style=\"width:25%\">Renewal Type:</td>
            <td style=\"width:25%\">" . $row["renewaltype"] . "</td>
            <td style=\"width:25%\"></td>
            <td style=\"width:25%\"></td>
        </tr>";
        switch ($row["renewaltypeid"]) {
            case 3:
                echo "<tr>
                    <td>Fixed %:</td>
                    <td colspan=\"3\">" . $row["fixedpercent"] . "%</td>

                </tr>";
            break;
            case 4:
                echo "<tr>
                    <td>Fixed %:</td>
                    <td colspan=\"3\">" . $row["fixedpercent"] . "%</td>
                </tr>";
            break;
            case 5:
                echo "<tr>
                    <td>Fixed %:</td>
                    <td colspan=\"3\">" . $row["fixedpercent"] . "%</td>
                </tr>";
            break;
            case 9:
                echo "<tr>
                    <td>Fixed %:</td>
                    <td colspan=\"3\">" . $row["fixedpercent"] . "%</td>
                </tr>";
            break;
            default:

        }
    echo "<tr>
            <td>Lease ID:</td>
            <td colspan=\"3\">" . $row["leaseid"] . "</td>
        </tr>
        <tr>
            <td>Start Date:</td>
            <td colspan=\"3\">" . date_format(date_create($row["startdate"]),"j F Y") . "</td>
        </tr>
        <tr>
            <td>End Date:</td>
            <td colspan=\"3\">";
            if (empty($row["enddate"])) {
                echo  "</td>";
            } else {
                echo "" . date_format(date_create($row["enddate"]),"j F Y") . "</td>";
            }
    echo
        "</tr>
        <tr>
            <td>Rent - Premises:</td>
            <td colspan=\"3\">$" . number_format($row["rentpremises"],2) . "</td>
        </tr>
        <tr>
            <td>Rent - Carparks:</td>
            <td colspan=\"3\">$" . number_format($row["rentcarparks"],2) . "</td>
        </tr>
        <tr>
            <td>Status:</td>
            <td colspan=\"3\">" . $row["renewalstatus"] . "</td>
        </tr>
        <tr>
            <td>Signed On:</td>
            <td colspan=\"3\">" . $row["renewalsignedon"] . "</td>
        </tr>
        <tr>
            <td>Signed By:</td>
            <td colspan=\"3\">" . $row["renewalsignedbyfirstname"] . " " . $row["renewalsignedbymiddlename"] . " " . $row["renewalsignedbylastname"] . "</td>
        </tr>
        <tr>";
//            if($row["rentpremises"] > 0) {
//                echo "<td colspan=\"2\" style=\"text-align:right;\"><a href=\"emailproposal.php?renewalid=" . $QPrenewalid . "&leaseid=" . $row["leaseid"] . "&premisesid=" . $row["idpremises"] . "&contenttype=renewal\">Send Proposal to Tenant</a></td>";
//                echo "<td colspan=\"2\" style=\"text-align:right;\"><a href=\"emailnotification.php?renewalid=" . $QPrenewalid . "&opexid=".$opexid."&opexstatusid=2&contenttype=renewal\">Send to Tenant</a></td>";
//            } else {
//                echo "<td colspan=\"2\" style=\"text-align:right;\">&nbsp;</td>";
//            }          
//        echo "</tr>";
  }
} else {
  echo "0 results";
}

echo "</tbody></table>";

echo "<div class=\"row\">
        <div class=\"col-sm-2\" style=\"padding-top:20px; padding-bottom:20px\"><a href=\"editrenewal.php?renewalid=" . $QPrenewalid . "\" class=\"btn btn-primary\">Edit Renewal</a></div>
        <div class=\"col-sm-6\" style=\"padding-top:20px; padding-bottom:20px\"><a href=\"viewlease.php?leaseid=" . $leaseid . "\" class=\"btn btn-primary\">Back to Lease</a></div>";
        if($rentpremises <> 0) {
            echo "<div class=\"col-sm-4\" style=\"text-align:right; padding-top:20px;\">";
            // If there is at least one active Renewal record
            if($result1->num_rows > 0) {
                if($isrightofrenewal == 1) {
                    echo"<a href=\"emailnotificationlegalror.php?renewalid=" . $QPrenewalid . "&opexid=".$opexid."&opexstatusid=2&contenttype=renewal\" class=\"btn btn-primary\">View Right of Renewal</a>";
                } else {
                    echo "Legal documnet not required";
                }
                echo "<a href=\"emailnotification.php?renewalid=" . $QPrenewalid . "&opexid=".$opexid."&opexstatusid=2&contenttype=renewal\" class=\"btn btn-primary\" style=\"margin-left:20px;\">View Tenant Statement</a></div>";
            } else {
                echo "No active OPEX records";
            }
        } else {
            echo "<div class=\"col-sm-4\" style=\"text-align:right; padding-top:20px;\">Premises rent is required to view notifications</div>";
        }

        $con->close();
?>

</div>

<?=template_footer()?>