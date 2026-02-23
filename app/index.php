<?php
//include 'appmain.php';

// If user already logged in, redirect to index
if (isset($_SESSION['account_loggedin'])) {
    header('Location: index.php');
    exit;
}
// Remember me cookie check
if (isset($_COOKIE['remember_me']) && !empty($_COOKIE['remember_me'])) {
    $stmt = $con->prepare('SELECT id, username, role FROM accounts WHERE remember_me_code = ?');
    $stmt->bind_param('s', $_COOKIE['remember_me']);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $username, $role);
        $stmt->fetch();
        $stmt->close();
        session_regenerate_id();
        $_SESSION['account_loggedin'] = TRUE;
        $_SESSION['account_name'] = $username;
        $_SESSION['account_id'] = $id;
        $_SESSION['account_role'] = $role;
        $date = date('Y-m-d\TH:i:s');
        $stmt = $con->prepare('UPDATE accounts SET last_seen = ? WHERE id = ?');
        $stmt->bind_param('si', $date, $id);
        $stmt->execute();
        $stmt->close();
        header('Location: index.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../app/css/styles.css" />
    <title>Lease Manager App</title>
</head>

<body>

<div class="center" style="margin-top:50px;">
<!--        <img src="../img/building_greyscale.png" height="100px">  -->
        <img src="../img/LeaseManager_logo.png" width="300px">
</div>

<div class="center" style="margin-bottom:50px;">
        <span class="title_thick">Lease</span><span class="title_thin" >Manager</span>
</div>

<div class="title_thin center" style="margin-bottom:50px;">
    Inspection App
</div>
    
<div class="center">
    <a href="login.php" class="blue-button btn-action" id="log-in-button">Log in</a>
</div>

</body>
</html>