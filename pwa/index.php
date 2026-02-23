<?php
include 'pwamain.php';

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

<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/pwa/service-worker.js');
        });
    }
</script>

<?= template_header('Home') ?>

<div class="center" style="margin:50px;">
        <img src="../img/LeaseManager_with_building_white_300.png">
</div>

<!--<div class="inspector" style="margin:50px;">
        Inspector
</div>-->
    
<div class="center">
    <a href="login.php" class="button btn-action" id="log-in-button">Log in</a>
</div>

</body>
</html>