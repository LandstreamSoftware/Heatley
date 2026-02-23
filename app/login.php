<?php
include 'appmain.php';

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
<!--
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../app/css/styles.css" />
    <title>Login</title>
</head>

<body>
-->

<?= template_header('Login') ?>

<div class="content">
    <form id="login-form" action="../authenticate.php" method="post">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" placeholder="username" required>
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="password" autocomplete="current-password" required>
        <!--<label><input type="checkbox" name="remember_me"> Remember me</label>-->
        <div class="center">
            <button class="blue-button medium_button" type="submit">Login</button>
        </div>
        <div class="msg"></div>
    </form>
</div>
<script>
    const form = document.getElementById('login-form');
    form.onsubmit = event => {
        event.preventDefault();
        fetch(form.action, { method: 'POST', body: new FormData(form), cache: 'no-store' })
            .then(r => r.text()).then(result => {
                const msg = form.querySelector('.msg');
                msg.textContent = '';
                if (result.toLowerCase().includes('redirect')) {
                    window.location.href = 'home.php';
                } else {
                    msg.textContent = result.replace('Error: ', '');
                }
            });
    };
</script>
</body>

</html>