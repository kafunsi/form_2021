<?php
require_once __DIR__ . '/config.php';
if (is_logged_in()) header('Location: projects.php');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($email === '' || $password === '') {
        $error = 'Email and password required.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            unset($user['password']);
            if (empty($user['role'])) {
                $user['role'] = 'viewer';
            }
            $_SESSION['user'] = $user;
            header('Location: projects.php');
            exit;
        } else {
            $error = 'Invalid credentials.';
        }
    }
}
?>
<?php include 'header.php'; ?>
<h2>Login</h2>
<?php if($error): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
<form method="post">
  <label>Email</label>
  <input name="email" type="email" required>
  <label>Password</label>
  <input name="password" type="password" required>
  <button type="submit">Login</button>
</form>
<?php include 'footer.php'; ?>
