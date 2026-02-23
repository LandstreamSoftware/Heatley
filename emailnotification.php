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
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width,minimum-scale=1">
		<title>Rent & OPEX Summary</title>
        <meta name="author" content="Barry Pyle" />
		<link href="style.css" rel="stylesheet" type="text/css">
		<link href="css/bootstrap.css" rel="stylesheet" type="text/css">
        <style>
            body {
                background-color: white !important;
            }
        </style>
	</head>

<?php

$Q = explode("/", $_SERVER['QUERY_STRING']);
parse_str($Q[0],$QueryParameters);
$QPcontenttype = $QueryParameters['contenttype'];
if(!empty($QueryParameters['renewalid'])) {
    $QPrenewalid = $QueryParameters['renewalid'];
}
if(!empty($QueryParameters['renewalstatusid'])) {
    $QPrenewalstatusid = $QueryParameters['renewalstatusid'];
}
if(!empty($QueryParameters['opexid'])) {
    $QPopexid = $QueryParameters['opexid'];
}

//Use to list the OPEX item
$sql = "SELECT * FROM notification_opex_view WHERE `idrenewals` = $QPrenewalid and `idopex` = $QPopexid and isunitspecific = 0 and recordOwnerID IN ($accessto)";
$result = $con->query($sql);

//Get one record for the email content
$sql2 = "SELECT * FROM notification_opex_view WHERE `idrenewals` = $QPrenewalid and `idopex` = $QPopexid and recordOwnerID IN ($accessto) LIMIT 1";
$result2 = $con->query($sql2);

$GST = 0.15;
$allocatedopextotal = 0;
$opextotal = 0;

//Build the content
while($row2 = $result2->fetch_assoc()) {

    $opexbudgetdatefrom = date_format(date_create($row2["opexdate"]),"Y");
    $opexbudgetdateto = date_format(date_add(date_create($row2["opexdate"]),date_interval_create_from_date_string("1 year")),"Y");

    $rentpremises = $row2["rentpremises"];
    $rentcarparks = $row2["rentcarparks"];

    $managementfeepercent = $row2["managementfeepercent"];

    $firstname = $row2["firstname"];
    $lastname = $row2["lastname"];
    $emailaddress = $row2["emailaddress"];

    $premisesid = $row2["idpremises"];
    $unitname = $row2["unitname"];

    // Unit specific Opex items
    $sql8 = "SELECT * FROM opexitemallocation_total_view WHERE premisesid = $premisesid";
    $result8 = $con->query($sql8);
    if ($result8->num_rows > 0) {
        while($row8 = $result8->fetch_assoc()) {
            $allocatedopextotal = $row8["allocatedcost"];
        }
    }

    //Get the rates URL
    $sql4 = "SELECT ratesURL FROM premises WHERE `idpremises` = $premisesid";
    $result4 = $con->query($sql4);
    if ($result4->num_rows > 0) {
        while($row4 = $result4->fetch_assoc()) {
            if($row4["ratesURL"] == "") {
                $isratesurl = 0;
                $ratesurl = "";
            } else {
                $isratesurl = 1;
                $ratesurl = $row4["ratesURL"];
            }
            
        }   
    }


echo " 
<div class=\"notification\">
    <div class=\"block\">

        <div class=\"col-sm-12\">
            <h5 style=\"font-weight:400;\">" . $row2["companyname"] . "</h5>
            <p>Premises: " . $row2["unitname"] . ", ". $row2["premisesaddress1"] . "<br>
            Area: " . $row2["floorarea"] . " m2</p>
        </div>";




//Renewal details
    //Get the OPEX total
        $sql3 = "SELECT * FROM notification_opextotal_view WHERE `opexid` = $QPopexid and isunitspecific = 0 and recordOwnerID IN ($accessto)";
        //$sql3 = "SELECT * FROM premises_opex_view WHERE idlease = $QPleaseid and opexstatusid = 2 and recordOwnerID IN ($accessto) LIMIT 1";
        $result3 = $con->query($sql3);
        while($row3 = $result3->fetch_assoc()) {
            $opextotal = $row3["opextotal"];
        }

        
        $opexpercentage = $row2["opexpercentage"];
        $opexportion = $opextotal * $opexpercentage / 100;
        $managementfee = ($rentpremises + $rentcarparks + $opexportion + $allocatedopextotal) * $managementfeepercent / 100;
        $permonthtotal = ($rentpremises + $rentcarparks + $opexportion + $allocatedopextotal + $managementfee) * (1 + $GST) / 12;
        
echo "<table style=\"width:100%; margin-left:auto; margin-right:auto; border:1px solid #555;\">
        <th class=\"printbggrey\" style=\"background-color:#eee; padding: 20px 10px; width:35%\">Item</th>
        <th class=\"printbggrey\" style=\"background-color:#eee; width:10%; text-align:right; padding-right:15px;\">Per Annum<br>(excl GST)</th>
        <th class=\"printbggrey\" style=\"background-color:#eee; width:5%;\">&nbsp;</th>
        <th class=\"printbggrey\" style=\"background-color:#eee; width:10%; text-align:right; padding-right:15px;\">Per Annum<br>(incl GST)</th>
        <th class=\"printbggrey\" style=\"background-color:#eee; width:5%;\">&nbsp;</th>
        <th class=\"printbggrey\" style=\"background-color:#eee; width:10%; text-align:right; padding-right:15px;\">Monthly<br>(excl GST)</th>
        <th class=\"printbggrey\" style=\"background-color:#eee; width:5%;\">&nbsp;</th>
        <th class=\"printbggrey\" style=\"background-color:#eee; width:10%; text-align:right; padding-right:15px;\">Monthly<br>(incl GST)</th>
        <tr>
            <td style=\"padding: 10px 10px;\">Rent (" . date_format(date_create($row2["startdate"]),"d/m/Y") . " to " . date_format(date_create($row2["enddate"]),"d/m/Y") . ")</td>
            <td style=\"padding: 0px 10px; text-align:right;\"><h5>$" . number_format($rentpremises + $rentcarparks,2) . "</h5></td>
            <td></td>
            <td style=\"padding: 0px 10px; text-align:right;\"><h5>$" . number_format(($rentpremises + $rentcarparks) * (1 + $GST),2) . "</h5></td>
            <td></td>
            <td style=\"padding: 0px 10px; text-align:right;\"><h5>$" . number_format(($rentpremises + $rentcarparks) / 12,2) . "</h5></td>
            <td></td>
            <td style=\"padding: 0px 10px; text-align:right;\"><h5>$" . number_format(($rentpremises + $rentcarparks) * (1 + $GST) / 12,2) . "</h5></td>
        </tr>
        <tr>
            <td style=\"padding: 10px 10px;\">OPEX ".$opexbudgetdatefrom."-".$opexbudgetdateto." Budget</td>
            <td style=\"padding: 0px 10px; text-align:right;\"><h5>$" . number_format($opextotal * $opexpercentage / 100,2) . "</h5></td>
            <td></td>
            <td style=\"padding: 0px 10px; text-align:right;\"><h5>$" . number_format($opextotal * $opexpercentage / 100 * (1 + $GST),2) . "</h5></td>
            <td></td>
            <td style=\"padding: 0px 10px; text-align:right;\"><h5>$" . number_format($opextotal * $opexpercentage / 100 / 12,2) . "</h5></td>
            <td></td>
            <td style=\"padding: 0px 10px; text-align:right;\"><h5>$" . number_format($opextotal * $opexpercentage / 100 * (1 + $GST) / 12,2) . "</h5></td>
        </tr>";
        
        // Unit specific Opex items
        if ($allocatedopextotal != 0) {
        echo 
        "<tr>
            <td style=\"padding: 10px 10px;\">Premises Specific Items (" . $unitname . ")</td>
            <td style=\"padding: 0px 10px; text-align:right;\"><h5>$" . number_format($allocatedopextotal,2) . "</h5></td>
            <td></td>
            <td style=\"padding: 0px 10px; text-align:right;\"><h5>$" . number_format($allocatedopextotal * (1 + $GST),2) . "</h5></td>
            <td></td>
            <td style=\"padding: 0px 10px; text-align:right;\"><h5>$" . number_format($allocatedopextotal / 12,2) . "</h5></td>
            <td></td>
            <td style=\"padding: 0px 10px; text-align:right;\"><h5>$" . number_format($allocatedopextotal * (1 + $GST) / 12,2) . "</h5></td>
        </tr>";
        }

        echo
       "<tr>
            <td style=\"padding: 10px 10px;\"><strong>Sub Total</strong></td>
            <td style=\"padding: 0px 10px; text-align:right; border-top:1px solid;\"><h5 style=\"font-weight:500;\">$" . number_format(($rentpremises + $rentcarparks + $opexportion + $allocatedopextotal),2) . "</h5></td>
            <td></td>
            <td style=\"padding: 0px 10px; text-align:right; border-top:1px solid;\"><h5 style=\"font-weight:500;\">$" . number_format(($rentpremises + $rentcarparks + $opexportion + $allocatedopextotal) * (1 + $GST),2) . "</h5></td>
            <td></td>
            <td style=\"padding: 0px 10px; text-align:right; border-top:1px solid;\"><h5 style=\"font-weight:500;\">$" . number_format(($rentpremises + $rentcarparks + $opexportion + $allocatedopextotal) / 12,2) . "</h5></td>
            <td></td>
            <td style=\"padding: 0px 10px; text-align:right; border-top:1px solid;\"><h5 style=\"font-weight:500;\">$" . number_format(($rentpremises + $rentcarparks + $opexportion + $allocatedopextotal) * (1 + $GST) / 12,2) . "</h5></td>
        </tr>
        <tr>
            <td style=\"padding: 10px 10px;\">Management Fee</td>
            <td style=\"padding: 0px 10px; text-align:right;\"><h5>$" . number_format(($rentpremises + $rentcarparks + $opexportion + $allocatedopextotal) * $managementfeepercent / 100,2) . "</h5></td>
            <td></td>
            <td style=\"padding: 0px 10px; text-align:right;\"><h5>$" . number_format((($rentpremises + $rentcarparks + $opexportion + $allocatedopextotal) * $managementfeepercent / 100) * (1 + $GST),2) . "</h5></td>
            <td></td>
            <td style=\"padding: 0px 10px; text-align:right;\"><h5>$" . number_format((($rentpremises + $rentcarparks + $opexportion + $allocatedopextotal) * $managementfeepercent / 100)/12,2) . "</h5></td>
            <td></td>
            <td style=\"padding: 0px 10px; text-align:right;\"><h5 style=\"font-weight:500;\">$" . number_format(number_format((($rentpremises + $rentcarparks + $opexportion + $allocatedopextotal) * $managementfeepercent / 100) / 12,2) * (1 + $GST),2) . "</h5></td>
        </tr>
        <tr>
            <td style=\"padding: 10px 10px;\"><strong>Total</strong></td>
            <td style=\"padding: 0px 10px; text-align:right; border-top:1px solid;\"><h5 style=\"font-weight:500;\">$" . number_format($rentpremises + $rentcarparks + $opexportion + $allocatedopextotal + $managementfee,2) . "</h5></td>
            <td></td>
            <td style=\"padding: 0px 10px; text-align:right; border-top:1px solid;\"><h5 style=\"font-weight:500;\">$" . number_format(($rentpremises + $rentcarparks + $opexportion + $allocatedopextotal + $managementfee) * (1 + $GST),2) . "</h5></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td style=\"padding: 10px 10px;\">GST</td>
            <td style=\"padding: 0px 10px; text-align:right;\"><h5>$" . number_format(($rentpremises + $rentcarparks + $opexportion + $allocatedopextotal + $managementfee) * $GST,2) . "</h5></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td style=\"padding: 10px 10px;\"><strong>Total Including GST</strong></td>
            <td style=\"padding: 0px 10px; text-align:right; border-top:1px solid;\"><h5 style=\"font-weight:500;\">$" . number_format(($rentpremises + $rentcarparks + $opexportion + $allocatedopextotal + $managementfee) * (1 + $GST),2) . "</h5></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr style=\"border-top: 1px solid;\">
            <td class=\"printbggreen\" colspan=\"7\" style=\"padding: 15px 10px; background-color:#dff0d8;\"><strong>Total to pay each month (incl. GST)</strong></td>
            <td class=\"printbggreen\" style=\"padding: 0px 10px; text-align:right; background-color:#dff0d8;\"><h5 style=\"font-weight:500;\">$" . number_format($permonthtotal,2) . "</h5></td>
        </tr>
    </table>
</div>";
}

//OPEX Budget details
echo "<div class=\"block\">
    <table style=\"width:100%; margin-left:auto; margin-right:auto; margin-top:40px; border:1px solid #555;\">
        <th class=\"printbggrey\" style=\"background-color:#eee; padding: 20px 10px; width:40%;\">OPEX ".$opexbudgetdatefrom."-".$opexbudgetdateto." Budget</th>
        <th class=\"printbggrey\" style=\"background-color:#eee; width:10%; text-align:right; padding-right:15px;\">Per Annum<br>(excl GST)</th>
        <th class=\"printbggrey\" style=\"background-color:#eee; width:5%;\">&nbsp;</th>
        <th class=\"printbggrey\" style=\"background-color:#eee; width:10%; text-align:right; padding-right:15px;\">Per Annum<br>(incl GST)</th>
        <th class=\"printbggrey\" style=\"background-color:#eee; width:5%;\">&nbsp;</th>
        <th class=\"printbggrey\" style=\"background-color:#eee; width:10%; text-align:right; padding-right:15px;\">Monthly<br>(excl GST)</th>
        <th class=\"printbggrey\" style=\"background-color:#eee; width:5%;\">&nbsp;</th>
        <th class=\"printbggrey\" style=\"background-color:#eee; width:10%; text-align:right; padding-right:15px;\">Monthly<br>(incl GST)</th>";
while($row = $result->fetch_assoc()) {

    $opexitemcost = $row["opexitemcost"];

echo "<tr>
        <td style=\"padding: 8px 10px;\">".$row["opexitemname"]."</td>
        <td style=\"padding: 0px 10px; text-align:right;\"><h5 style=\"font-size:16px;\">$".number_format($opexitemcost,2)."<h5></td>
        <td></td>
        <td style=\"padding: 0px 10px; text-align:right;\"><h5 style=\"font-size:16px;\">$".number_format($opexitemcost * (1 + $GST),2)."<h5></td>
        <td></td>
        <td style=\"padding: 0px 10px; text-align:right;\"><h5 style=\"font-size:16px;\">$".number_format($opexitemcost / 12,2)."</h5></td>
        <td></td>
        <td style=\"padding: 0px 10px; text-align:right;\"><h5 style=\"font-size:16px;\">$".number_format($opexitemcost / 12 * (1 + $GST),2)."</h5></td>
    </tr>";
}
echo "<tr>
        <td style=\"padding: 8px 10px;\"><strong>Total</strong></td>
        <td style=\"padding: 0px 10px; border-top:1px solid; text-align:right;\"><h5 style=\"font-weight:500;\">$" . number_format($opextotal,2) . "</h5></td>
        <td></td>
        <td style=\"padding: 0px 10px; border-top:1px solid; text-align:right;\"><h5 style=\"font-weight:500;\">$" . number_format($opextotal * (1 + $GST),2) . "</h5></td>
        <td></td>
        <td style=\"padding: 0px 10px; border-top:1px solid; text-align:right;\"><h5 style=\"font-weight:500;\">$" . number_format($opextotal / 12,2) . "</h5></td>
        <td></td>
        <td style=\"padding: 0px 10px; border-top:1px solid; text-align:right;\"><h5 style=\"font-weight:500;\">$" . number_format($opextotal / 12 * (1 + $GST),2) . "</h5></td>
    </tr>
    <tr style=\"border-top: 1px solid;\">
        <td class=\"printbggreen\" style=\"background-color:#dff0d8; padding: 15px 10px;\"><strong>Your portion of the OPEX: ".$opexpercentage."%</strong></td>
        <td class=\"printbggreen\" style=\"background-color:#dff0d8; padding: 0px 10px; text-align:right;\"><h5 style=\"font-weight:500;\">$" . number_format($opextotal * $opexpercentage / 100,2) . "</h5></td>
        <td class=\"printbggreen\" style=\"background-color:#dff0d8;\"></td>
        <td class=\"printbggreen\" style=\"background-color:#dff0d8; padding: 0px 10px; text-align:right;\"><h5 style=\"font-weight:500;\">$" . number_format($opextotal * $opexpercentage / 100 * (1 + $GST),2) . "</h5></td>
        <td class=\"printbggreen\" style=\"background-color:#dff0d8;\"></td>
        <td class=\"printbggreen\" style=\"background-color:#dff0d8; padding: 0px 10px; border-top:1px solid; text-align:right;\"><h5 style=\"font-weight:500;\">$" . number_format($opextotal * $opexpercentage / 100 / 12,2) . "</h5></td>
        <td class=\"printbggreen\" style=\"background-color:#dff0d8;\"></td>
        <td class=\"printbggreen\" style=\"background-color:#dff0d8; padding: 0px 10px; border-top:1px solid; text-align:right;\"><h5 style=\"font-weight:500;\">$" . number_format($opextotal * $opexpercentage / 100 / 12 * (1 + $GST),2) . "</h5></td>
    </tr>
</table>";


//OPEX Budget details
if ($allocatedopextotal > 0 || $isratesurl > 0) {
echo "<div class=\"block\">
    <table style=\"width:100%; margin-left:auto; margin-right:auto; margin-top:40px; border:1px solid #555;\">
        <th class=\"printbggrey\" style=\"background-color:#eee; padding: 20px 10px; width:35%;\">Premises Specific Items</th>
        <th class=\"printbggrey\" style=\"background-color:#eee; width:10%; text-align:right; padding-right:15px;\">Per Annum<br>(excl GST)</th>
        <th class=\"printbggrey\" style=\"background-color:#eee; width:5%;\">&nbsp;</th>
        <th class=\"printbggrey\" style=\"background-color:#eee; width:10%; text-align:right; padding-right:15px;\">Per Annum<br>(incl GST)</th>
        <th class=\"printbggrey\" style=\"background-color:#eee; width:5%;\">&nbsp;</th>
        <th class=\"printbggrey\" style=\"background-color:#eee; width:10%; text-align:right; padding-right:15px;\">Monthly<br>(excl GST)</th>
        <th class=\"printbggrey\" style=\"background-color:#eee; width:5%;\">&nbsp;</th>
        <th class=\"printbggrey\" style=\"background-color:#eee; width:10%; text-align:right; padding-right:15px;\">Monthly<br>(incl GST)</th>";

    $sql9 = "SELECT * FROM opexitemallocation_view WHERE premisesid = $premisesid";
    $result9 = $con->query($sql9);
    $totalallocatedcost = 0;
    if ($result9->num_rows > 0) {
        while($row9 = $result9->fetch_assoc()) {
            $allocatedcost = $row9["allocatedcost"];
            $totalallocatedcost += $allocatedcost;
        echo "<tr>
                <td style=\"padding: 8px 10px;\">".$row9["opexitemname"]."</td>
                <td style=\"padding: 0px 10px; text-align:right;\"><h5 style=\"font-size:16px;\">$" . number_format($allocatedcost,2) . "<h5></td>
                <td></td>
                <td style=\"padding: 0px 10px; text-align:right;\"><h5 style=\"font-size:16px;\">$" . number_format($allocatedcost * (1 + $GST),2) . "<h5></td>
                <td></td>
                <td style=\"padding: 0px 10px; text-align:right;\"><h5 style=\"font-size:16px;\">$" . number_format($allocatedcost / 12,2) . "</h5></td>
                <td></td>
                <td style=\"padding: 0px 10px; text-align:right;\"><h5 style=\"font-size:16px;\">$" . number_format($allocatedcost / 12 * (1 + $GST),2) . "</h5></td>
            </tr>";
        }
    }

    echo "<tr>
        <td style=\"padding: 8px 10px;\"><a href=\"" . $ratesurl . "\">Rating Information Details</a></td>
    </tr>";


    echo
    "<tr style=\"border-top: 1px solid;\">
        <td class=\"printbggreen\" style=\"background-color:#dff0d8; padding: 15px 10px;\"><strong>Total premises specific items:</strong></td>
        <td class=\"printbggreen\" style=\"background-color:#dff0d8; padding: 0px 10px; text-align:right;\"><h5 style=\"font-weight:500;\">$" . number_format($totalallocatedcost,2) . "</h5></td>
        <td class=\"printbggreen\" style=\"background-color:#dff0d8;\"></td>
        <td class=\"printbggreen\" style=\"background-color:#dff0d8; padding: 0px 10px; text-align:right;\"><h5 style=\"font-weight:500;\">$" . number_format($totalallocatedcost * (1 + $GST),2) . "</h5></td>
        <td class=\"printbggreen\" style=\"background-color:#dff0d8;\"></td>
        <td class=\"printbggreen\" style=\"background-color:#dff0d8; padding: 0px 10px; border-top:1px solid; text-align:right;\"><h5 style=\"font-weight:500;\">$" . number_format($totalallocatedcost / 12,2) . "</h5></td>
        <td class=\"printbggreen\" style=\"background-color:#dff0d8;\"></td>
        <td class=\"printbggreen\" style=\"background-color:#dff0d8; padding: 0px 10px; border-top:1px solid; text-align:right;\"><h5 style=\"font-weight:500;\">$" . number_format($totalallocatedcost / 12 * (1 + $GST),2) . "</h5></td>
    </tr>";
}

echo "</div>

<div class=\"block\">
    <table style=\"width:100%; margin-left:auto; margin-right:auto; margin-top:40px;\">
        <tr><td style=\"text-align:center;\">Powered by Lease Manager</td></tr>
    </table>
</div>

</div>";
$con->close();
?>