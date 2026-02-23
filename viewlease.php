<?php
// Include the main.php file
include 'main.php';
// Check if the user is logged in, if not then redirect to login page
check_loggedin($con);
// Template code below

$accountid = $_SESSION['account_id'];

$sqlAccess = "SELECT * FROM accesscontrol WHERE accountID = $accountid";
$resultAccess = $con->query($sqlAccess);

$accessto = 0;

if ($resultAccess->num_rows > 0) {
    while($rowAccess = $resultAccess->fetch_assoc()) {
       $accessto .= "," . $rowAccess["companyID"]; 
    }
}
?>

<?=template_header('View Lease')?>

<div class="page-title">
	<div class="icon">
		<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z"/></svg>
	</div>	
	<div class="wrap">
		<h2>Lease Details</h2>
<!--		<p>Welcome back, <?=htmlspecialchars($_SESSION['account_name'], ENT_QUOTES)?>!</p>  -->
	</div>
</div>

<div class="block">
    

<?php
$Q = explode("/", $_SERVER['QUERY_STRING']);
parse_str($Q[0],$QueryParameters);
if(empty($QueryParameters['leaseid'])){
    $QPleaseid = "";
}else{
    $QPleaseid = $QueryParameters['leaseid'];
}
if(empty($QPrenewalid)){
    $QPrenewalid ="";
}else{
    $QPrenewalid = $QueryParameters['renewalid'];
}
$totalmonthlyrent = 0;
$managementfee = 0;
$annualmanagementfee = 0;
$monthlymanagementfee = 0;
$managementfeepercent = 0;
$opexbudgetdatefrom = "";
$opexbudgetdateto = "";
$opextotal = 0;
$opexpercentage = 0;
$opexportion = 0;
$permonthtotal = 0;
$GST = 0.15;
$currentrent = 0;
$rentpremises = 0;
$rentcarparks = 0;
$allocatedopextotal = 0;

$bucketname = gcloud_bucket_leases;

?>

<table class="table">

<?php
$sql = "SELECT * FROM leases_view WHERE idlease = $QPleaseid and recordOwnerID IN ($accessto)";
$result = $con->query($sql);

$sql2 = "SELECT * FROM currentrent_view WHERE idlease = $QPleaseid and recordOwnerID IN ($accessto) ORDER BY startdate DESC LIMIT 1";
$result2 = $con->query($sql2);

$sql3 = "SELECT * FROM premises_opex_view WHERE idlease = $QPleaseid and opexstatusid = 2 and recordOwnerID IN ($accessto) LIMIT 1";
$result3 = $con->query($sql3);

$sql5 = "SELECT * FROM leasestatus";
$result5 = $con->query($sql5);

$sql6 = "SELECT * FROM rentreviews_view WHERE leaseid = $QPleaseid";
$result6 = $con->query($sql6);

if ($result->num_rows > 0) {

    // output data of each row
    while($row = $result->fetch_assoc()) {

        $tenantid = $row["tenantid"];

        $sql4 = "SELECT * FROM contacts WHERE companyID = $tenantid and recordOwnerID IN ($accessto)";
        $result4 = $con->query($sql4);

        $sql7 = "SELECT * from files WHERE bucketName = '$bucketname' and recordID = '$QPleaseid'";
  	    $result7 = $con->query($sql7);
        $premisesid = $row["premisesid"];
        $unitname = $row["unitname"];

        // Unit specific Opex items
        $sql8 = "SELECT * FROM opexitemallocation_total_view WHERE premisesid = $premisesid";
        $result8 = $con->query($sql8);
        if ($result8->num_rows > 0) {
            
            while($row8 = $result8->fetch_assoc()) {
                $allocatedopextotal = $row8["allocatedcost"];
            }
        }

        $sql9 = "SELECT * from files WHERE bucketName IS null and recordID = '$QPleaseid'";
  	    $result9 = $con->query($sql9);

        echo "<tr>
            <td style=\"width:25%\">Tenant Name:</td>
            <td style=\"width:25%\"><a class=\"h4link\" href=\"/viewcompany.php?companyid=" . $row["tenantid"] . "\">" . $row["tenantname"] . "</a></td><td></td>
            <td style=\"width:25%\"></td>
            <td style=\"width:25%\"></td>
        </tr>
        <tr>
            <td>Premises:</td><td colspan=\"3\"><a href=\"viewpremises.php?premisesid=" . $row["premisesid"] . "\">" . $row["unitname"] . ", " . $row["premisesaddress1"] . "</a></td><td></td>
        </tr>
        <tr style=\"padding:0px;\">
            <td>Commencement Date:</td><td colspan=\"2\">" . date_format(date_create($row["commencement"]),"j F Y") . "</td>
            <td colspan=\"2\" rowspan=\"5\" style=\" padding:0px;\">
                <table class=\"table table-borderless\" style=\"margin:0px; border: 1px solid #555;\">
                    <th style=\"font-weight:bold; width:28%; background-color:#efefef;\">Rent Reviews:</th>
                    <th style=\"font-weight:normal; background-color:#efefef; text-align:right;\"><a href=\"addrentreview.php?leaseid=".$QPleaseid."\">Add Rent Review</a></th>
                    <tbody style=\"height:100px;\">";
                    while($row6 = $result6->fetch_assoc()) {
                        echo "<tr><td><a href=\"editrentreview.php?rentreviewid=".$row6["idrentreview"]."\">".$row6["rentreviewtypename"]." dates:</a></td>
                        <td>".$row6["rentreviewdetails"]."</tr>";
                    }
                echo "</tbody></table>
            </td>
        </tr>
        <tr>
            <td>Term:</td><td colspan=\"2\">" . $row["term"] . " years</td>

        </tr>
        <tr>
            <td>Rights of Renewal:</td><td colspan=\"2\">" . $row["rightsofrenewal"] . "</td>

        </tr>
        <tr>
            <td>Expiry Date:</td><td colspan=\"2\">" . date_format(date_create($row["leaseexpirydate"]),"j F Y") . "</td>

        </tr>
        <tr>
            <td>Annual Rent - Premises:</td><td colspan=\"2\">$" . number_format($row["annualrentpremises"],2) . "</td>
        </tr>
        <tr>
            <td>Annual Rent - Carparks:</td><td colspan=\"3\">$" . number_format($row["annualrentcarparks"],2) . "</td><td></td>
        </tr>
        <tr>
            <td>Invoice Date:</td>
            <td colspan=\"2\">";

            switch($row["invoicedate"]) {
                case '1':
                    echo  $row["invoicedate"] . "st of the rental month";
                    break;
                case '0':
                    echo  "20th of the month prior";
                    break;
                default:
                    echo  "Anniversary of commencement date (" . $row["invoicedate"] . ")";
            }
            echo "</td>
            <td colspan=\"2\" rowspan=\"7\" style=\"padding:0;\">
                <table class=\"table table-borderless\" style=\"margin:0px; border: 1px solid #555;\">
                    <th style=\"font-weight:bold; width:28%;\">Lease Documents:</th>
                    <tbody style=\"height:100px;\">
                    <tr><td>";
                    while($row7 = $result7->fetch_assoc()) {
                        $filepath = $row7["filePath"];
                        $originalname = $row7["originalName"];
                        $file_url = "https://storage.cloud.google.com/" . gcloud_bucket_leases . "/" . $filepath;
                        echo "<img src='img/pdf_logo.png' height='25px' style='margin:5px 10px 5px 0;'><a href=\"" .  $file_url . "\">" . $originalname . "</a><br>";
                    }
                    while($row9 = $result9->fetch_assoc()) {
                        $filepath = $row9["filePath"];
                        $originalname = $row9["originalName"];
                        $file_url = $filepath;
                        echo "<img src='img/document_logo.png' height='25px' style='margin:5px 10px 5px 0;'><a href=\"" .  $file_url . "\" target=\"_blank\">" . $originalname . "</a><br>";
                    }
                echo "</td></tr></tbody></table>
            </td>
        </tr>
        
        <tr>
            <td>Signed On date:</td><td colspan=\"2\">";
            if($row["signedon"] == NULL) {
                echo "</td>";
            } else {
                echo date_format(date_create($row["signedon"]),"j F Y") . "</td>";
            }
        echo "
        </tr>
        <tr>
            <td>Signed By:</td><td colspan=\"2\"><a href=\"/viewcontact.php?contactid=" . $row["signedbyid"] . "\">" . $row["signedbyfirstname"] . " " . $row["signedbymiddlename"] . " " . $row["signedbylastname"] . "</a></td>
        </tr>
        <tr>
            <td>Guarantor:</td><td colspan=\"2\"><a href=\"/viewcontact.php?contactid=" . $row["guarantorid"] . "\">" . $row["guarantorfirstname"] . " " . $row["guarantormiddlename"] . " " . $row["guarantorlastname"] . "</a></td>
        </tr>
        <tr>
            <td>Bond:</td><td colspan=\"2\">$" . number_format($row["bondamount"],2) . "</td>
        </tr>
        <tr>
            <td>Status:</td><td colspan=\"2\">" . $row["leasestatus"] . "</td>
        </tr>
        <tr>
            <td>Property Management Company:</td><td colspan=\"2\">" . $row["propertymanagercompany"] . "</td>
        </tr>
        <tr>
            <td>Property Manager Contact:</td><td colspan=\"2\">" . $row["propertymanagercontactfirstname"] . " " . $row["propertymanagercontactlastname"] . "</td>
        </tr>
        <tr>
            <td>Contacts:</td><td colspan=\"3\">";
    }

    if ($result4->num_rows > 0) {
        while($row4 = $result4->fetch_assoc()) {
            echo "<a href=\"viewcontact.php?contactid=" . $row4["idcontacts"] . "\">". $row4["firstName"] . " " . $row4["lastName"] . " - " . $row4["emailAddress"] ."</a><br>";       
        }
    }
        echo "<td><a href=\"/viewleaseterms.php?leaseid=" . $QPleaseid . "\" class=\"btn btn-primary\">Further Terms of Lease</a></td>
        </tr>
        
    </table>";

    if ($result2->num_rows > 0) {
    while($row2 = $result2->fetch_assoc()) {
        $currentrent = $row2["rentpremises"] + $row2["rentcarparks"];
        $rentpremises = $row2["rentpremises"];
        $rentcarparks = $row2["rentcarparks"];
        $totalmonthlyrent += $currentrent/12;
    }
    }

    if ($result3->num_rows > 0) {
    while($row3 = $result3->fetch_assoc()) {
        $opextotal = $row3["opexamount"];
        $opexpercentage = $row3["opexpercentage"];
        $opexportion = $opextotal * $opexpercentage / 100;
        $managementfeepercent = $row3["managementfeepercent"];
        $managementfee = ($rentpremises + $rentcarparks + $opexportion + $allocatedopextotal) * $managementfeepercent / 100;
        $permonthtotal = ($rentpremises + $rentcarparks + $opexportion + $allocatedopextotal + $managementfee) * (1 + $GST) / 12;
        $opexbudgetdatefrom = date_format(date_create($row3["opexdate"]),"Y");
        $opexbudgetdateto = date_format(date_add(date_create($row3["opexdate"]),date_interval_create_from_date_string("1 year")),"Y");
    }
    }

    echo "<table style=\"width:75%; margin-bottom:15px; border: 1px solid #555;\">
        <th class=\"printbggrey\" style=\"background-color:#eee; padding: 20px 10px; width:35%\">Item</th>
        <th class=\"printbggrey\" style=\"background-color:#eee; width:10%; text-align:right; padding-right:15px;\">Per Annum<br>(excl GST)</th>
        <th class=\"printbggrey\" style=\"background-color:#eee; width:5%;\">&nbsp;</th>
        <th class=\"printbggrey\" style=\"background-color:#eee; width:10%; text-align:right; padding-right:15px;\">Per Annum<br>(incl GST)</th>
        <th class=\"printbggrey\" style=\"background-color:#eee; width:5%;\">&nbsp;</th>
        <th class=\"printbggrey\" style=\"background-color:#eee; width:10%; text-align:right; padding-right:15px;\">Monthly<br>(excl GST)</th>
        <th class=\"printbggrey\" style=\"background-color:#eee; width:5%;\">&nbsp;</th>
        <th class=\"printbggrey\" style=\"background-color:#eee; width:10%; text-align:right; padding-right:15px;\">Monthly<br>(incl GST)</th>
        <tr>
            <td style=\"padding: 10px 10px;\">Rent</td>
            <td style=\"padding: 0px 10px; text-align:right;\"><h5>$" . number_format($rentpremises + $rentcarparks,2) . "</h5></td>
            <td></td>
            <td style=\"padding: 0px 10px; text-align:right;\"><h5>$" . number_format(($rentpremises + $rentcarparks) * (1 + $GST),2) . "</h5></td>
            <td></td>
            <td style=\"padding: 0px 10px; text-align:right;\"><h5>$" . number_format(($rentpremises + $rentcarparks) / 12,2) . "</h5></td>
            <td></td>
            <td style=\"padding: 0px 10px; text-align:right;\"><h5>$" . number_format(($rentpremises + $rentcarparks) * (1 + $GST) / 12,2) . "</h5></td>
        </tr>
        <tr>
            <td style=\"padding: 10px 10px;\">OPEX " . $opexbudgetdatefrom . "-" . $opexbudgetdateto . " Budget (" . $opexpercentage . "% of total Opex)</td>
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
            <td style=\"padding: 0px 10px; text-align:right;\"><h5 style=\"font-weight:500;\">$" . number_format(($rentpremises + $rentcarparks + $opexportion + $allocatedopextotal) * $managementfeepercent / 100 / 12 * 1.15,2) . "
            </h5></td>
        </tr>
        <tr>
            <td style=\"padding: 10px 10px;\"><strong>Total</strong></td>
            <td style=\"padding: 0px 10px; text-align:right; border-top:1px solid;\"><h5 style=\"font-weight:500;\">$" . number_format(($rentpremises + $rentcarparks + $opexportion + $allocatedopextotal + $managementfee),2) . "</h5></td>
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
        </tr>";
    /* end rent and opex table*/
} else {
  echo "";
}

echo "</tbody>";

?>

</table>

<?php

echo
"<div class=\"row\">
        <div class=\"col-sm-2\" style=\"padding-top:20px; padding-bottom:20px\"><a href=\"editlease.php?leaseid=" . $QPleaseid . "\" class=\"btn btn-primary\">Edit Lease</a></div>
</div>

<div>
    <h3 style=\"padding:15px 0 15px 0;\">Renewals, Rent Reviews and Deed of Variations:</h3>
</div>";


?>

<div class="row">&nbsp;</div>


<table class="table">
<thead>
        <tr>
            <th>Renewal Type</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th style="text-align:right;">Rent Premises</th>
            <th style="text-align:right;">Rent Carparks</th>
            <th style="text-align:center;">Status</th>
            <th></th>
        </tr>
    </thead>
    <tbody>

<?php
$sqlren = "SELECT * FROM renewals_view WHERE leaseid = $QPleaseid and statusdisplayorder < 5 and recordOwnerID IN ($accessto) ORDER BY startdate ASC";
$resultren = $con->query($sqlren);

if ($resultren->num_rows > 0) {
    // output data of each row
    while($row5 = $resultren->fetch_assoc()) {

    $thisdate = new DateTime();
    $resultdate = $thisdate->format('Y-m-d');
    $renewaltypeid = $row5["renewaltypeid"];

    if ($row5["renewalstatusid"] == 3) {
            echo "<tr class=\"alert-success\">";
        } else {
            echo "<tr>";
        }

    echo
            "<td>" . $row5["renewaltype"];
            if($row5["fixedpercent"] > 0) {
                echo " (" . $row5["fixedpercent"] . "%)";
            }
    echo    "</td>
            <td>" . date_format(date_create($row5["startdate"]),"j F Y"). "</td>
            <td>";
            if (empty($row5["enddate"])) {
                echo  "</td>";
            } else {
                echo "" . date_format(date_create($row5["enddate"]),"j F Y") . "</td>";
            }
            echo
            "<td style=\"text-align:right;\">$" . number_format($row5['rentpremises'],2). "</td>
            <td style=\"text-align:right;\">$" . number_format($row5["rentcarparks"],2) . "</td>
            <td style=\"text-align:center;\">" . $row5["renewalstatus"] . "</td>
            <td><a href=\"viewrenewal.php?renewalid=".  $row5["idrenewals"] . "&leaseid=" . $QPleaseid . "\">VIEW</a></td>
        </tr>";
    }
} else {
  echo "";
}

echo "</tbody></table>";

echo
    "<div class=\"row\" style=\"margin-top:20px; margin-bottom:40px;\">
          <div class=\"col-sm-6\" style=\"padding-top:20px; padding-bottom:20px;\"><a href=\"addrenewal.php?leaseid=" . $QPleaseid . "\" class=\"btn btn-primary\">Add Renewal</a></div>
          <div class=\"col-sm-6\" style=\"padding-top:20px; padding-bottom:20px; text-align:right;\"><a href=\"listrenewalsexpired.php?leaseid=" . $QPleaseid . "\">Show archived Renewals</a></div>
    </div>";

    $con->close();
?>


<?=template_footer()?>