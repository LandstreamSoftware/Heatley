<?php
ob_start();
include 'main.php';
// Save the email templates
if (isset($_POST['activation_email_template'])) {
    if (file_put_contents('../activation-email-template.html', $_POST['activation_email_template']) === false) {
        header('Location: email_templates.php?error_msg=1');
        exit;
    }
}
if (isset($_POST['notification_email_template'])) {
    if (file_put_contents('../notification-email-template.html', $_POST['notification_email_template']) === false) {
        header('Location: email_templates.php?error_msg=1');
        exit;
    }
}
if (isset($_POST['twofactor_email_template'])) {
    if (file_put_contents('../twofactor-email-template.html', $_POST['twofactor_email_template']) === false) {
        header('Location: email_templates.php?success_msg=1');
        exit;
    }
}
if (isset($_POST['resetpass_email_template'])) {
    if (file_put_contents('../resetpass-email-template.html', $_POST['resetpass_email_template']) === false) {
        header('Location: email_templates.php?success_msg=1');
        exit;
    }
}
if (isset($_POST['email_template_renewal_notification'])) {
    if (file_put_contents('../email-template-renewal-notification.html', $_POST['email_template_renewal_notification']) === false) {
        header('Location: email_templates.php?success_msg=1');
        exit;
    }
}
if (isset($_POST['submit'])) {
    header('Location: email_templates.php?success_msg=1');
    exit;
}
// Read the activation email template HTML file
if (file_exists('../activation-email-template.html')) {
    $activation_email_template = file_get_contents('../activation-email-template.html');
}
// Read the notification email template HTML file
if (file_exists('../notification-email-template.html')) {
    $notification_email_template = file_get_contents('../notification-email-template.html');
}
// Read the two-factor email template
if (file_exists('../twofactor-email-template.html')) {
    $twofactor_email_template = file_get_contents('../twofactor-email-template.html');
}
// Read the reset password email template HTML file
if (file_exists('../resetpass-email-template.html')) {
    $resetpass_email_template = file_get_contents('../resetpass-email-template.html');
}
// Read the renewal reminder notification email template HTML file
if (file_exists('../email-template-renewal-notification.html')) {
    $email_template_renewal_notification = file_get_contents('../email-template-renewal-notification.html');
}
// Handle success messages
if (isset($_GET['success_msg'])) {
    if ($_GET['success_msg'] == 1) {
        $success_msg = 'Email template updated successfully!';
    }
}
// Handle error messages
if (isset($_GET['error_msg'])) {
    if ($_GET['error_msg'] == 1) {
        $error_msg = 'There was an error updating the email template! Please set the correct permissions!';
    }
}
?>
<?=template_admin_header('Email Templates', 'email_templates')?>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post" enctype="multipart/form-data">
    <div class="content-title responsive-flex-wrap responsive-pad-bot-3">
        <h2 class="responsive-width-100">Email Templates</h2>
        <input type="submit" name="submit" value="Save" class="btn">
    </div>
    <?php if (isset($success_msg)): ?>
    <div class="mar-top-4">
        <div class="msg success">
            <svg width="14" height="14" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM369 209L241 337c-9.4 9.4-24.6 9.4-33.9 0l-64-64c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l47 47L335 175c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9z"/></svg>
            <p><?=$success_msg?></p>
            <svg class="close" width="14" height="14" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M342.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192 210.7 86.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L146.7 256 41.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 301.3 297.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.3 256 342.6 150.6z"/></svg>
        </div>
    </div>
    <?php endif; ?>
    <?php if (isset($error_msg)): ?>
    <div class="mar-top-4">
        <div class="msg error">
            <svg width="14" height="14" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zm0-384c13.3 0 24 10.7 24 24V264c0 13.3-10.7 24-24 24s-24-10.7-24-24V152c0-13.3 10.7-24 24-24zM224 352a32 32 0 1 1 64 0 32 32 0 1 1 -64 0z"/></svg>
            <p><?=$error_msg?></p>
            <svg class="close" width="14" height="14" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M342.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192 210.7 86.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L146.7 256 41.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 301.3 297.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.3 256 342.6 150.6z"/></svg>
        </div>
    </div>
    <?php endif; ?>
    <div class="tabs">
        <?php if (isset($activation_email_template)): ?>
        <a href="#" class="active">Activation</a>
        <?php endif; ?>
        <?php if (isset($notification_email_template)): ?>
        <a href="#">Notification</a>
        <?php endif; ?>
        <?php if (isset($twofactor_email_template)): ?>
        <a href="#">Two-factor</a>
        <?php endif; ?>
        <?php if (isset($resetpass_email_template)): ?>
        <a href="#">Reset Password</a>
        <?php endif; ?>
        <?php if (isset($email_template_renewal_notification)): ?>
        <a href="#">Renewal Reminder</a>
        <?php endif; ?>
    </div>
    <div class="content-block">
        <div class="form responsive-width-100 size-md">
            <?php if (isset($activation_email_template)): ?>
            <div class="tab-content active">
                <label for="activation_email_template">Activation Email Template:</label>
                <textarea name="activation_email_template" id="activation_email_template" class="code-editor"><?=$activation_email_template?></textarea>
            </div>
            <?php endif; ?>
            <?php if (isset($notification_email_template)): ?>
            <div class="tab-content">
                <label for="notification_email_template">Notification Email Template:</label>
                <textarea name="notification_email_template" id="notification_email_template" class="code-editor"><?=$notification_email_template?></textarea>
            </div>
            <?php endif; ?>
            <?php if (isset($twofactor_email_template)): ?>
            <div class="tab-content">
                <label for="twofactor_email_template">Two-factor Email Template:</label>
                <textarea name="twofactor_email_template" id="twofactor_email_template" class="code-editor"><?=$twofactor_email_template?></textarea>
            </div>
            <?php endif; ?>
            <?php if (isset($resetpass_email_template)): ?>
            <div class="tab-content">
                <label for="resetpass_email_template">Reset Password Email Template:</label>
                <textarea name="resetpass_email_template" id="resetpass_email_template" class="code-editor"><?=$resetpass_email_template?></textarea>
            </div>
            <?php endif; ?>
            <?php if (isset($email_template_renewal_notification)): ?>
            <div class="tab-content">
                <label for="email_template_renewal_notification">Renewal Reminder Email Template:</label>
                <textarea name="email_template_renewal_notification" id="email_template_renewal_notification" class="code-editor"><?=$email_template_renewal_notification?></textarea>
            </div>
            <?php endif; ?>
        </div>
    </div>
</form>
<?=template_admin_footer()?>