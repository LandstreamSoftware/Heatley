<?php
include 'main.php';
// Default account values
$account = [
    'firstname' => '',
    'lastname' => '',
    'username' => '',
    'password' => '',
    'email' => '',
    'activation_code' => 'pending',
    'remember_me_code' => '',
    'role' => 'User',
    'approved' => 0,
    'registered' => date('Y-m-d\TH:i'),
    'last_seen' => date('Y-m-d\TH:i'),
    'companyid' => ''
];
// List of Companies that a user can be assigned to - Property Owner or Property Manager
$sql = "SELECT idcompany, companyName from companies WHERE companyTypeID in (2,5)";
$result = $con->query($sql);
// If editing an account
if (isset($_GET['id'])) {
    // Get the account from the database
    $stmt = $con->prepare('SELECT firstname, lastname, username, password, email, activation_code, remember_me_code, role, registered, last_seen, approved, companyID FROM accounts WHERE id = ?');
    $stmt->bind_param('i', $_GET['id']);
    $stmt->execute();
    $stmt->bind_result($account['firstname'], $account['lastname'], $account['username'], $account['password'], $account['email'], $account['activation_code'], $account['rememberme'], $account['role'], $account['registered'], $account['last_seen'], $account['approved'], $account['companyid']);
    $stmt->fetch();
    $stmt->close();
    // ID param exists, edit an existing account
    $page = 'Edit';
    if (isset($_POST['submit'])) {
        // Check to see if username already exists
        $stmt = $con->prepare('SELECT id FROM accounts WHERE username = ? AND username != ?');
        $stmt->bind_param('ss', $_POST['username'], $account['username']);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error_msg = 'Username already exists!';
        }
        // Check to see if email already exists
        $stmt = $con->prepare('SELECT id FROM accounts WHERE email = ? AND email != ?');
        $stmt->bind_param('ss', $_POST['email'], $account['email']);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error_msg = 'Email already exists!';
        }
        // Update the account
        if (!isset($error_msg)) {
            $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : $account['password'];
            $activation_code = $_POST['activation_status'] == 'activated' || $_POST['activation_status'] == 'deactivated' ? $_POST['activation_status'] : $_POST['activation_code'];
            $stmt = $con->prepare('UPDATE accounts SET firstname = ?, lastname = ?, username = ?, password = ?, email = ?, activation_code = ?, remember_me_code = ?, role = ?, registered = ?, last_seen = ?, approved = ?, companyID = ? WHERE id = ?');
            $stmt->bind_param('ssssssssssiii', $_POST['firstname'], $_POST['lastname'], $_POST['username'], $password, $_POST['email'], $activation_code, $_POST['rememberme'], $_POST['role'], $_POST['registered'], $_POST['last_seen'], $_POST['approved'], $_POST['companyid'], $_GET['id']);
            $stmt->execute();
            header('Location: accounts.php?success_msg=2');
            exit;
        } else {
            // Update the account variables
            $account = [
                'firstname' => '',
                'lastname' => '',
                'username' => $_POST['username'],
                'password' => $_POST['password'],
                'email' => $_POST['email'],
                'activation_code' => $_POST['activation_code'],
                'remember_me_code' => $_POST['rememberme'],
                'role' => $_POST['role'],
                'approved' => $_POST['approved'],
                'registered' => $_POST['registered'],
                'last_seen' => $_POST['last_seen'],
                'companyid' => $_POST['companyID']
            ];
        }
    }
    if (isset($_POST['delete'])) {
        // Redirect and delete the account
        header('Location: accounts.php?delete=' . $_GET['id']);
        exit;
    }
} else {
    // Create a new account
    $page = 'Create';
    if (isset($_POST['submit'])) {
        // Check to see if username already exists
        $stmt = $con->prepare('SELECT id FROM accounts WHERE username = ?');
        $stmt->bind_param('s', $_POST['username']);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error_msg = 'Username already exists!';
        }
        // Check to see if email already exists
        $stmt = $con->prepare('SELECT id FROM accounts WHERE email = ?');
        $stmt->bind_param('s', $_POST['email']);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error_msg = 'Email already exists!';
        }
        // Insert new account into the database
        if (!isset($error_msg)) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $activation_code = $_POST['activation_status'] == 'activated' || $_POST['activation_status'] == 'deactivated' ? $_POST['activation_status'] : $_POST['activation_code'];
            $stmt = $con->prepare('INSERT IGNORE INTO accounts (firstname,lastname,username,password,email,activation_code,remember_me_code,role,registered,last_seen,approved,companyID) VALUES (?,?,?,?,?,?,?,?,?,?)');
            $stmt->bind_param('ssssssssssii', $_POST['firstname'], $_POST['lastname'], $_POST['username'], $password, $_POST['email'], $activation_code, $_POST['rememberme'], $_POST['role'], $_POST['registered'], $_POST['last_seen'], $_POST['approved'], $_POST['companyid']);
            $stmt->execute();
            header('Location: accounts.php?success_msg=1');
            exit;
        } else {
            // Update the account variables
            $account = [
                'firstname' => $_POST['firstname'],
                'lastname' => $_POST['lastname'],
                'username' => $_POST['username'],
                'password' => $_POST['password'],
                'email' => $_POST['email'],
                'activation_code' => $_POST['activation_code'],
                'remember_me_code' => $_POST['rememberme'],
                'role' => $_POST['role'],
                'approved' => $_POST['approved'],
                'registered' => $_POST['registered'],
                'last_seen' => $_POST['last_seen'],
                'companyid' => $_POST['companyID']
            ];
        }
    }
}
?>
<?=template_admin_header($page . ' Account', 'accounts', 'manage')?>

<form action="" method="post" enctype="multipart/form-data">

    <div class="content-title responsive-flex-wrap responsive-pad-bot-3">
        <h2 class="responsive-width-100"><?=$page?> Account</h2>
        <a href="accounts.php" class="btn alt mar-right-2">Cancel</a>
        <?php if ($page == 'Edit'): ?>
        <input type="submit" name="delete" value="Delete" class="btn red mar-right-2" onclick="return confirm('Are you sure you want to delete this account?')">
        <?php endif; ?>
        <input type="submit" name="submit" value="Save" class="btn">
    </div>

    <?php if (isset($error_msg)): ?>
    <div class="mar-top-4">
        <div class="msg error">
            <svg width="14" height="14" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zm0-384c13.3 0 24 10.7 24 24V264c0 13.3-10.7 24-24 24s-24-10.7-24-24V152c0-13.3 10.7-24 24-24zM224 352a32 32 0 1 1 64 0 32 32 0 1 1 -64 0z"/></svg>
            <p><?=$error_msg?></p>
            <svg class="close" width="14" height="14" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M342.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192 210.7 86.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L146.7 256 41.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 301.3 297.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.3 256 342.6 150.6z"/></svg>
        </div>
    </div>
    <?php endif; ?>

    <div class="content-block">

        <div class="form responsive-width-100">

            <label for="firstname"><span class="required">*</span> First Name</label>
            <input type="text" id="firstname" name="firstname" placeholder="First name" value="<?=$account['firstname']?>" required>
            
            <label for="lastname"><span class="required">*</span> Last Name</label>
            <input type="text" id="lastname" name="lastname" placeholder="Last name" value="<?=$account['lastname']?>" required>
            
            <label for="username"><span class="required">*</span> Username</label>
            <input type="text" id="username" name="username" placeholder="Username" value="<?=$account['username']?>" required>

            <label for="password"><?=$page == 'Edit' ? 'New ' : '<span class="required">*</span> '?>Password</label>
            <input type="password" id="password" name="password" placeholder="<?=$page == 'Edit' ? 'New ' : ''?>Password" autocomplete="new-password" value=""<?=$page == 'Edit' ? '' : ' required'?>>

            <label for="email"><span class="required">*</span> Email</label>
            <input type="text" id="email" name="email" placeholder="Email" value="<?=$account['email']?>" required>

            <label for="activation_status">Activation Status</label>
            <select id="activation_status" name="activation_status" style="margin-bottom: 30px;">
                <option value="activated"<?=$account['activation_code']=='activated'?' selected':''?>>Activated</option>
                <option value="deactivated"<?=$account['activation_code']=='deactivated'?' selected':''?>>Deactivated</option>
                <option value="pending"<?=$account['activation_code']!='activated'&&$account['activation_code']!='deactivated'?' selected':''?>>Pending</option>
            </select>

            <div class="activation_code" style="display:<?=$account['activation_code']=='activated'||$account['activation_code']=='deactivated'?' none':' block'?>">
                <label for="activation_code">Activation Code</label>
                <input type="text" id="activation_code" name="activation_code" placeholder="Activation Code" value="<?=$account['activation_code']?>">
            </div>

            <label for="approved">Approved</label>
            <select id="approved" name="approved" style="margin-bottom: 30px;">
                <option value="1"<?=$account['approved']==1?' selected':''?>>Yes</option>
                <option value="0"<?=$account['approved']==0?' selected':''?>>No</option>
            </select>

            <label for="rememberme">Remember Me Code</label>
            <input type="text" id="rememberme" name="rememberme" placeholder="Remember Me Code" value="<?=$account['remember_me_code']?>">

            <label for="role">Role</label>
            <select id="role" name="role" style="margin-bottom: 30px;">
                <?php foreach ($roles_list as $role): ?>
                <option value="<?=$role?>"<?=$role==$account['role']?' selected':''?>><?=$role?></option>
                <?php endforeach; ?>
            </select>



            <label for="companyid">User's Company</label>
            <select id="companyid" name="companyid" style="margin-bottom: 30px;">
                <option value="">- Select a Company -</option>
                <option value="0">SYSTEM ADMINISTRATOR</option>
            <?php if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    if($row["idcompany"] == $account['companyid']){
                        echo "<option value=\"" . $row["idcompany"] . "\" selected>". $row["companyName"] . "</option>";
                    } else {
                        echo "<option value=\"" . $row["idcompany"] . "\">" . $row["companyName"] . "</option>";
                    }
                }
            }
            ?>
            </select>





            <label for="registered">Registered Date</label>
            <input id="registered" type="datetime-local" name="registered" value="<?=date('Y-m-d\TH:i', strtotime($account['registered']))?>" required>
        
            <label for="last_seen">Last Seen Date</label>
            <input id="last_seen" type="datetime-local" name="last_seen" value="<?=date('Y-m-d\TH:i', strtotime($account['last_seen']))?>" required>
    <?php   if (isset($_GET['id'])) { ?>
                <a href="accesscontrol.php?id=<?=$_GET['id']?>" class="btn alt mar-right-2">Edit Access Control</a>
    <?php   } ?>
        </div>

    </div>

</form>

<script>
document.getElementById('activation_status').addEventListener('change', function() {
    if (this.value == 'activated' || this.value == 'deactivated') {
        document.querySelector('.activation_code').style.display = 'none';
        document.querySelector('#activation_code').value = this.value;
    } else {
        document.querySelector('.activation_code').style.display = 'block';
        document.querySelector('#activation_code').value = '';
        document.querySelector('#activation_code').focus();
    }
});
</script>

<?=template_admin_footer()?>