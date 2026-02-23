<?php
// Include the pwamain.php file
include 'pwamain.php';
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

<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/pwa/service-worker.js')
                .then((registration) => {
                    console.log('Service Worker registered with scope:', registration.scope);
                })
                .catch((error) => {
                    console.log('Service Worker registration failed:', error);
                });
        });
    }
</script>

<?= template_header('Saved Inspections') ?>

<?php
// define variables and set to empty values
$buildingid = $premisesid = $area = $notes = $conditionid = "";
$buildingidErr = $premisesidErr = $areaErr = $notesErr = $conditionidErr = NULL;

$Q = explode("/", $_SERVER['QUERY_STRING']);
parse_str($Q[0], $QueryParameters);
if (empty($QueryParameters['ti'])) {
    $QPtypeid = "";
} else {
    $QPtypeid = $QueryParameters['ti'];
}
if (empty($QueryParameters['bi'])) {
    $QPbuildingid = "";
} else {
    $QPbuildingid = $QueryParameters['bi'];
}
if (empty($QueryParameters['pr'])) {
    $QPpremisesid = "";
} else {
    $QPpremisesid = $QueryParameters['pr'];
}
if (empty($QueryParameters['co'])) {
    $QPconditionid = "";
} else {
    $QPconditionid = $QueryParameters['co'];
}


//Query for dropdowns
$sql2 = "SELECT idbuildings, buildingName FROM buildings WHERE recordOwnerID IN ($accessto) ORDER BY buildingName";
$result2 = $con->query($sql2);
$sql3 = "SELECT * FROM inspectiontype ORDER BY idinspectiontype";
$result3 = $con->query($sql3);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST["buildingid"])) {
        $buildingidErr = "Building is required";
    } else {
        $buildingid = test_input($_POST["buildingid"]);
        //check if the field only contains numbers
        if (!preg_match("/^[0-9' ]*$/", $buildingid)) {
            $buildingidErr = "Only numbers allowed";
        }
    }

    if (empty($_POST["premisesid"])) {
        $premisesidErr = "Premises is required";
    } else {
        $premisesid = test_input($_POST["premisesid"]);
        //check if the field only contains letters dash or white space
        if (!preg_match("/^[0-9' ]*$/", $premisesid)) {
            $premisesidErr = "Only numbers allowed";
        }
    }

    if (empty($_POST["area"])) {
        $areaErr = "Area is required";
    } else {
        $area = test_input($_POST["area"]);
        //check if the field only contains letters dash or white space
        if (!preg_match("/^[a-zA-Z-0-9āēīōūĀĒĪŌŪ' ]*$/", $area)) {
            $areaErr = "Only letters, numbers, dash and spaces allowed";
        }
    }

    if (!empty($_POST["notes"])) {
        $notes = test_input($_POST["notes"]);
        //check if the field only contains letters dash or white space
        if (!preg_match("/^[a-zA-Z-0-9āēīōūĀĒĪŌŪ'. ]*$/", $notes)) {
            $notesErr = "Only letters, numbers, dash and spaces allowed";
        }
    }

    if (empty($_POST["conditionid"])) {
        $conditionidErr = "Condition is required";
    } else {
        $conditionid = test_input($_POST["conditionid"]);
        //check if the field only contains letters dash or white space
        if (!preg_match("/^[0-9' ]*$/", $conditionid)) {
            $conditionidErr = "Only numbers allowed";
        }
    }
}

function test_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>

<div class="center">
    <button class="button btn-action" id="sync-button">Sync Saved Inspections</button>
</div>

<div id="inspections-list"></div>

<div class="center">
    <button class="button btn-action" id="clear-saved-list">Clear Saved Inspections</button>
</div>

<script src="/pwa/assets/js/app.js"></script>
<!--<script>
        const inspectionId = Date.now();
        const link = document.getElementById('add-room-link');
        document.getElementById('inspectionid').value = inspectionId;
        //link.href = 'addroom.php?inspectionid=' + inspectionId;
        const openWindowURL = "addroom.php?inspectionid=" + inspectionId;
    </script>-->
<script>
    function addRoomWindow(url) {
        var myWindow = window.open(openWindowURL, "", "width=300,height=300,top=30,left=300");
    }
</script>
<script>
    // Register the service worker
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/pwa/service-worker.js').then(() => {
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
</body>

</html>