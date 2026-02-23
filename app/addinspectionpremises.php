<?php
// Include the appmain.php file
include 'appmain.php';
// Check if the user is logged in, if not then redirect to login page
check_loggedin($con);
// Template code below

$accountid = $_SESSION['account_id'];

$sqlAccess = "SELECT * FROM accesscontrol WHERE accountID = $accountid";
$resultAccess = $con->query($sqlAccess);

$accessto = -1;

if ($resultAccess->num_rows > 0) {
    while ($rowAccess = $resultAccess->fetch_assoc()) {
        $accessto .= "," . $rowAccess["companyID"];
    }
}

?>

<?= template_header('Add Inspection') ?>

<?php
$Q = explode("/", $_SERVER['QUERY_STRING']);
parse_str($Q[0],$QueryParameters);
//$Query = $QueryParameters['premisesid'];
if(empty($QueryParameters['bid'])){
    $QPbid = "";
}else{
    $QPbid = $QueryParameters['bid'];
}

//Query for dropdowns
$sql = "SELECT * FROM premises_details_view WHERE idbuildings = $QPbid and recordownerid IN ($accessto) ORDER BY unitname";
$result = $con->query($sql);
//$sql3 = "SELECT * FROM inspectiontype ORDER BY idinspectiontype";
//$result3 = $con->query($sql3);

echo '<h1>Select the Premises...</h1>';

while ($row = $result->fetch_assoc()) {
    $premisesid = $row["idpremises"];
    $sql3 = "SELECT inspectionDate FROM inspections WHERE premisesID = premisesid order by inspectionDate DESC LIMIT 1";
    $result3 = $con->query($sql3);
    if ($result3->num_rows > 0) {
        while ($row3 = $result3->fetch_assoc()) {
            $lastinspection = $row3["inspectionDate"];
        }
    } else {
        $lastinspection = '';
    }

    
    echo '<div class="panel">
    <div class="panel-title">'
        . $row["unitname"] . 
    '</div>
    <div>Last inspection: '
    . $lastinspection .
    '</div>
    <div class="center">
        <a href="addinspectiontype.php?bid=' . $QPbid . '&pid=' . $premisesid . '" type="button" class="blue-button full_button">Select</a>
    </div>
    </div>
    ';

}


?>

<!--
<script>
    // Register the service worker
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/pwa/service-worker.js').then(() => {
            console.log('Service Worker registered.');
        });
    }
</script>
-->
<script>
    function updatePremisesList() {
        const buildingId = Number(document.getElementById("buildingid").value);
        const buildingName = document.getElementById("buildingname");
        const premisesName = document.getElementById("premisesname");
        const premisesId = document.getElementById("premisesid");
        const premisesURL = "../get_leasepremises.php?buildingid=" + buildingId;
        if (buildingId) {
            fetch(premisesURL)
                .then(response => response.json())
                .then(data => {
                    // Clear any previous values
                    premisesId.value = '';
                    premisesId.innerHTML = '<option value=\"0\"> - Select premises - </option>';
                    // Populate the Premises dropdown list
                    data.forEach(item => {
                        const option = document.createElement("option");
                        option.value = item.idpremises;
                        option.textContent = item.unitname;
                        premisesId.appendChild(option);
                    });
                });

        }
    }
</script>
<script>
    function updateAreasList() {
        const premisesId = Number(document.getElementById("premisesid").value);
        const unitName = document.getElementById("premisesid").options[document.getElementById("premisesid").selectedIndex].text;
        const premisesName = document.getElementById("premisesname");
        const areaId = document.getElementById("area");
        const premisesURL = "../get_leasepremises.php?premisesid=" + premisesId;
        if (premisesId) {
            //areaId.innerHTML = '<option value="">-- Select Area --</option>';
            fetch(premisesURL)
                .then(response => response.json())
                .then(data => {
                    // Populate the Areas dropdown list
                    data.forEach(item => {
                        //const option = document.createElement("option");
                        //option.value = item.idinspectionroom;
                        //option.textContent = item.roomname;
                        //areaId.appendChild(option);
                    });
                });
        }
    }
</script>
</body>

</html>