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

<?=template_header('Further Terms of Lease')?>

<div class="page-title">
	<div class="icon">
		<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z"/></svg>
	</div>	
	<div class="wrap">
		<h2>Further Terms of Lease</h2>
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
$leasetermname = $leasetermtext = $leasetermgrouping = $furthertermstext = "";

    ?>
    <script>
        function confirmDelete() {
            return confirm("Are you sure you want to remove this clause from the lease?");
        }
    </script>
    <?php

    echo "<table class=\"table\">";

    $sql = "SELECT * FROM leaseterms_view WHERE leaseid = $QPleaseid and recordownerid IN ($accessto) ORDER BY clausenumber";
    $result = $con->query($sql);

    $sql2 = "SELECT furtherTerms FROM leases WHERE idlease = $QPleaseid";
    $result2 = $con->query($sql2);

    if ($result->num_rows > 0) {

    // output data of each row
        while($row = $result->fetch_assoc()) {
            $furthertermstext .= "," . $row["leasetermgrouping"];
            if($leasetermgrouping != $row["leasetermgrouping"]) {
                $leasetermgrouping = $row["leasetermgrouping"];
                echo "<th colspan= \"3\" class=\"printbggrey\" style=\"background-color:#eee; padding: 10px 10px; width:20%\">" . $leasetermgrouping . "</th>";
            }
        echo "<tr>
                <td style=\"padding-top: 10px; font-weight:bold;\">";
                //remove the leading 0 from the clauseNumber.
                echo ltrim($row["clausenumber"],"0");
        echo "</td>
                <td style=\"padding: 10px 30px 40px 30px;\">" . nl2br(htmlspecialchars($row["leasetermtext"])) . "</td>
                <td>
                    <a href=\"editmoreleaseterms.php?mappingid=" . $row['leasetermsmappingid'] . "\" class=\"btn btn-primary\"> modify </a>
                    <form method=\"POST\" action=\"deleteleasetermsclause.php\" onsubmit=\"return confirmDelete();\">
                        <input type=\"hidden\" name=\"leasetermsmappingid\" value=\"".$row['leasetermsmappingid']."\">
                        <input type=\"hidden\" name=\"leaseid\" value=\"".$row['leaseid']."\">
                        <button class=\"btn alt\" type=\"submit\" style=\"margin-top:10px;\">remove</button>
                    </form>
                </td>
            </tr>";
        }
        echo "</table>";
    }

        echo "<div class=\"row\" style=\"margin-top:20px;\">
            <div class=\"col-sm-2\" style=\"padding-top:20px;\"><a href=\"viewlease.php?leaseid=".$QPleaseid."\" class=\"btn btn-primary\">Back to Lease</a></div>
            <div class=\"col-sm-2\" style=\"padding-top:20px;\"><a href=\"addmoreleaseterms.php?leaseid=".$QPleaseid."\" class=\"btn btn-primary\">Add Clause</a></div>
            <div class=\"col-sm-8\" style=\"padding-top:20px; text-align:right;\"><a href=\"emailnotificationclauses.php?leaseid=".$QPleaseid ."\" class=\"btn btn-primary\">Print Clause List</a></div>
        </div>";
    
    

$con->close();
?>

</div>

<?=template_footer()?>