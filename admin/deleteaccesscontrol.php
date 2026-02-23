<?php
include 'main.php';
// Current date
$date = date('Y-m-d\TH:i:s');
// Prepare roles query
$roles = $con->query('SELECT role, COUNT(*) as total FROM accounts GROUP BY role')->fetch_all(MYSQLI_ASSOC);
$roles = array_column($roles, 'total', 'role');
foreach ($roles_list as $r) {
    if (!isset($roles[$r])) $roles[$r] = 0;
}
// Get active accounts
$roles_active = $con->query('SELECT role, COUNT(*) as total FROM accounts WHERE last_seen > date_sub("' . $date . '", interval 1 month) GROUP BY role')->fetch_all(MYSQLI_ASSOC);
$roles_active = array_column($roles_active, 'total', 'role');
// Get inactive accounts
$roles_inactive = $con->query('SELECT role, COUNT(*) as total FROM accounts WHERE last_seen < date_sub("' . $date . '", interval 1 month) GROUP BY role')->fetch_all(MYSQLI_ASSOC);
$roles_inactive = array_column($roles_inactive, 'total', 'role');


$Q = explode("/", $_SERVER['QUERY_STRING']);
parse_str($Q[0],$QueryParameters);
$QPaccountid = $QueryParameters['id'];

$companyidErr = "";
$currentaccess = "0";


// Prepare access query
$access = $con->query('SELECT companyname, companyid FROM accesscontrol_view WHERE accountid = "' . $QPaccountid . '"')->fetch_all(MYSQLI_ASSOC);
$access = array_column($access, 'companyname');

$accessid = array_column($access, 'companyid');


// Full ist of Comanies that a user can be granted access to
$sql1 = 'SELECT companyid FROM accesscontrol_view WHERE accountid = "' . $QPaccountid . '"';
$result1 = $con->query($sql1);
if ($result1->num_rows > 0) {
    while($row1 = $result1->fetch_assoc()) {
        $currentaccess .= "," . $row1["companyid"];
    } 
}
//List of Companies not yet granted access to
$sql = "SELECT idcompany, companyName from companies WHERE companyTypeID in (2,5) and idcompany NOT IN ($currentaccess)";
$result = $con->query($sql);

//Get the user account
$sql2 = 'SELECT firstname, lastname, username FROM accounts WHERE id = "' . $QPaccountid . '"';
$result2 = $con->query($sql2);
while($row2 = $result2->fetch_assoc()) {
    $accountname = $row2["firstname"] . " " . $row2["lastname"] . " (" . $row2["username"] . ")";
}

?>
<?=template_admin_header('Access Control', 'accesscontrol')?>

<?php


function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" and isset($_POST['accesscontrolid'])) {
    $accesscontrolid = intval($_POST['accesscontrolid']);

    //prepare the delete statement to prevent SQL injection
    $stmt = $con->prepare("DELETE FROM accesscontrol WHERE idaccesscontrol = ?");
    $stmt->bind_param('i', $accesscontrolid);

    if ($stmt->execute()) {
        echo '<div class=\"row\">
        <table class="table table-hover">
            <tbody>
                <tr class="success">
                    <td>Record deleted successfully</td>
                </tr>
            </tbody>
        </table>';
    } else {
        echo 'Error deleting record: ' . $con->error;
    }
} else {


?>

<div class="content-title">
    <div class="title">
        <div class="icon">
            <svg width="18" height="18" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M224 0a128 128 0 1 1 0 256A128 128 0 1 1 224 0zM178.3 304h91.4c11.8 0 23.4 1.2 34.5 3.3c-2.1 18.5 7.4 35.6 21.8 44.8c-16.6 10.6-26.7 31.6-20 53.3c4 12.9 9.4 25.5 16.4 37.6s15.2 23.1 24.4 33c15.7 16.9 39.6 18.4 57.2 8.7v.9c0 9.2 2.7 18.5 7.9 26.3H29.7C13.3 512 0 498.7 0 482.3C0 383.8 79.8 304 178.3 304zM436 218.2c0-7 4.5-13.3 11.3-14.8c10.5-2.4 21.5-3.7 32.7-3.7s22.2 1.3 32.7 3.7c6.8 1.5 11.3 7.8 11.3 14.8v17.7c0 7.8 4.8 14.8 11.6 18.7c6.8 3.9 15.1 4.5 21.8 .6l13.8-7.9c6.1-3.5 13.7-2.7 18.5 2.4c7.6 8.1 14.3 17.2 20.1 27.2s10.3 20.4 13.5 31c2.1 6.7-1.1 13.7-7.2 17.2l-14.4 8.3c-6.5 3.7-10 10.9-10 18.4s3.5 14.7 10 18.4l14.4 8.3c6.1 3.5 9.2 10.5 7.2 17.2c-3.3 10.6-7.8 21-13.5 31s-12.5 19.1-20.1 27.2c-4.8 5.1-12.5 5.9-18.5 2.4l-13.8-7.9c-6.7-3.9-15.1-3.3-21.8 .6c-6.8 3.9-11.6 10.9-11.6 18.7v17.7c0 7-4.5 13.3-11.3 14.8c-10.5 2.4-21.5 3.7-32.7 3.7s-22.2-1.3-32.7-3.7c-6.8-1.5-11.3-7.8-11.3-14.8V467.8c0-7.9-4.9-14.9-11.7-18.9c-6.8-3.9-15.2-4.5-22-.6l-13.5 7.8c-6.1 3.5-13.7 2.7-18.5-2.4c-7.6-8.1-14.3-17.2-20.1-27.2s-10.3-20.4-13.5-31c-2.1-6.7 1.1-13.7 7.2-17.2l14-8.1c6.5-3.8 10.1-11.1 10.1-18.6s-3.5-14.8-10.1-18.6l-14-8.1c-6.1-3.5-9.2-10.5-7.2-17.2c3.3-10.6 7.7-21 13.5-31s12.5-19.1 20.1-27.2c4.8-5.1 12.4-5.9 18.5-2.4l13.6 7.8c6.8 3.9 15.2 3.3 22-.6c6.9-3.9 11.7-11 11.7-18.9V218.2zm92.1 133.5a48.1 48.1 0 1 0 -96.1 0 48.1 48.1 0 1 0 96.1 0z"/></svg>
        </div>
        <div class="txt">
            <h2>Access Control</h2>
            <p>Give access to an Acount's data.</p>
        </div>
    </div>
</div>

<div class="content-block">
    <div class="table">
        <table>
            <tbody>
                <tr>
                    <td>Access removed</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>





<?php } ?>

<?=template_admin_footer()?>