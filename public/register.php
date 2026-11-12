<?php
require __DIR__ . '/../app/bootstrap.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $first=input($_POST,'first_name'); $last=input($_POST,'last_name');
    $email=input($_POST,'email'); $username=input($_POST,'username');
    $password=isset($_POST['password']) && is_string($_POST['password']) ? $_POST['password'] : '';
    if (!$first || !$last || strlen($first)>100 || strlen($last)>100 || !filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($email)>254 || !preg_match('/^[A-Za-z0-9_]{3,50}$/D',$username) || strlen($password)<12 || strlen($password)>72) {
        $error='Enter names (up to 100 bytes each), a valid email, a 3–50 character username (letters, numbers, underscores), and a password of 12–72 bytes.';
    } else {
        $pdo=db(); $pdo->beginTransaction();
        try {
            query('INSERT INTO contacts (first_name,last_name,email) VALUES (?,?,?)',[$first,$last,$email]);
            $contactId=$pdo->lastInsertId();
            $role=query("SELECT role_id FROM roles WHERE role_type='member'")->fetchColumn();
            if (!$role) throw new RuntimeException('Member role missing. Import seed data.');
            query('INSERT INTO users (contact_id,role_id) VALUES (?,?)',[$contactId,$role]);
            $userId=$pdo->lastInsertId();
            query('INSERT INTO credentials (user_id,username,password_hash) VALUES (?,?,?)',[$userId,$username,password_hash($password,PASSWORD_DEFAULT)]);
            $pdo->commit(); session_regenerate_id(true); $_SESSION['user_id']=(int)$userId; unset($_SESSION['csrf']);
            header('Location: index.php'); exit;
        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $error=$ex instanceof PDOException && (int)($ex->errorInfo[1] ?? 0)===1062 ? 'That email or username is already registered.' : 'Registration failed. Check that schema and seed files were imported.';
            error_log('Moviezzz registration error: '.$ex->getCode());
        }
    }
}
page_start('Create your account');
?>
<?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
<form method="post" class="stack">
<input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
<label>First name<input name="first_name" required maxlength="100" value="<?= e(input($_POST,'first_name')) ?>"></label>
<label>Last name<input name="last_name" required maxlength="100" value="<?= e(input($_POST,'last_name')) ?>"></label>
<label>Email<input type="email" name="email" required maxlength="254" value="<?= e(input($_POST,'email')) ?>"></label>
<label>Username<input name="username" pattern="[A-Za-z0-9_]{3,50}" required value="<?= e(input($_POST,'username')) ?>" autocomplete="username"></label>
<label>Password<input type="password" name="password" required minlength="12" maxlength="72" autocomplete="new-password"></label>
<p class="muted">Use 12–72 bytes for your password; non-ASCII characters can use multiple bytes.</p><button>Create account</button>
</form>
<?php page_end(); ?>
