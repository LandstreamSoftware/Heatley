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

<?= template_header('Home') ?>

<h1>Home</h1>

<div class="panel">
    <div class="center">
        Click Start to create a new inspection
    </div>
    <div class="center">
        <a href="addinspectionproperty.php" type="button" class="blue-button full_button">Start</a>
    </div>
</div>


<!--
<script>
    // Register the service worker
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/app/service-worker.js').then(() => {
            console.log('Service Worker registered.');
        });
    }
</script>

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
-->
</body>

</html>