<?php
require_once __DIR__ . '/config.php';
if (is_logged_in()) header('Location: projects.php');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = ($_POST['role'] === 'submitter') ? 'submitter' : 'viewer';
    if ($name === '' || $email === '' || $password === '') {
        $error = 'All fields are required.';
    } else {
        // check existing
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email already registered.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (name,email,password,role) VALUES (?,?,?,?)');
            $stmt->execute([$name,$email,$hash,$role]);
            $id = $pdo->lastInsertId();
            $_SESSION['user'] = ['id'=>$id,'name'=>$name,'email'=>$email,'role'=>$role];
            header('Location: projects.php');
            exit;
        }
    }
}
?>
<?php include 'header.php'; ?>
<h2>Register</h2>
<?php if($error): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
<form method="post">
  <label>Name</label>
  <input name="name" required>
  <label>Email</label>
  <input name="email" type="email" required>
  <label>Password</label>
  <input name="password" type="password" required>
  <label>Role</label>
  <select name="role">
    <option value="submitter">Submitter (toa wazo)</option>
    <option value="viewer">Viewer (tazama mawazo)</option>
  </select>
  <button type="submit">Register</button>
</form>
<?php include 'footer.php'; ?>
