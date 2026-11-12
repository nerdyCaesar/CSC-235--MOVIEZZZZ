<?php
require __DIR__ . '/../app/bootstrap.php';
$error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    check_csrf();
    $u=query('SELECT u.user_id, c.password_hash FROM users u JOIN credentials c ON c.user_id=u.user_id WHERE c.username=? AND u.active=1',[input($_POST,'username')])->fetch();
    $password=isset($_POST['password']) && is_string($_POST['password']) ? $_POST['password'] : '';
    if ($u && password_verify($password,$u['password_hash'])) {
        session_regenerate_id(true); $_SESSION['user_id']=(int)$u['user_id']; unset($_SESSION['csrf']);
        header('Location: index.php'); exit;
    }
    $error='Incorrect username or password.';
}
page_start('Log in');
?>
<?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
<form method="post" class="stack"><input type="hidden" name="csrf" value="<?= e(csrf()) ?>"><label>Username<input name="username" required autocomplete="username"></label><label>Password<input type="password" name="password" required autocomplete="current-password"></label><button>Log in</button></form>
<?php page_end(); ?>
