<?php
require_once __DIR__ . '/config.php';
$error = '';
if (is_admin_logged_in()) {
    header('Location: admin_comments.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = trim($_POST['password'] ?? '');
    if ($password === 'kafunsi') {
        $_SESSION['admin'] = true;
        header('Location: admin_comments.php');
        exit;
    }
    $error = 'Password si sahihi.';
}
?>
<?php include 'header.php'; ?>
<h2>Admin Login</h2>
<?php if($error): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
<form method="post">
  <label>Admin Password</label>
  <input name="password" type="password" required>
  <button type="submit">Login</button>
</form>
<?php include 'footer.php'; ?>
