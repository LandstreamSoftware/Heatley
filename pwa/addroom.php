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
    while($rowAccess = $resultAccess->fetch_assoc()) {
       $accessto .= "," . $rowAccess["companyID"]; 
    }
}

$sqluser = "SELECT * FROM accounts WHERE id = $accountid";
$resultuser = $con->query($sqluser);

while($rowuser = $resultuser->fetch_assoc()) {
    $mycompanyid = $rowuser["companyID"]; 
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

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
    <title>Add Area</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/pwa/css/styles.css" />
</head>

<?php
// define variables and set to empty values
$roomname =  "";
$roomnameErr = NULL;

$Q = explode("/", $_SERVER['QUERY_STRING']);
parse_str($Q[0],$QueryParameters);
if(empty($QueryParameters['inspectionid'])){
    $inspectionid = "";
}else{
    $inspectionid = $QueryParameters['inspectionid'];
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (empty($_POST["roomname"])) {
    $roomnameErr = "Area Name is required";
  } else {
    $roomname = test_input($_POST["roomname"]);
    //check if the field only contains numbers
    if (!preg_match("/^[a-zA-Z-0-9āēīōūĀĒĪŌŪ' ]*$/", $roomname)) {
      $roomnameErr = "Only letters, numbers, dash and spaces allowed";
    }
  }
}

function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

?>

<body>
    <div class="heading-wrapper">
        <div class="heading">
            <div><a href="/pwa/index.php"><img src="/pwa/assets/img/home_icon.svg" alt="Home" class="home-icon"></a></div>
            <div>Add Area</div>
            <div></div>
    </div>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" and $roomnameErr == NULL) {


        echo "<div class=\"center\">
            <button class=\"button btn-action\" type=\"submit\" onclick=\"closeWindow\">Close</button>
        </div>
        <script>window.close();</script>";
    
} else {
?>
    <form id="add-room-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] .'?inspectionid='.$inspectionid);?>"></form>
        <label>
            Area Name:
            <input type="text" id="roomname" name="roomname">
        </label>
        <?php echo $roomnameErr?>
        <!-- Hidden fields for display purposes -->
        <input type="hidden" id="inspectionid" name="inspectionid" value="">
        <div class="center">
            <button class="button btn-action" type="submit">Save</button>
        </div>
    </form>
<?php
}
?>
</body>
</html>