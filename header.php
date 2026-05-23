<?php require_once __DIR__ . '/config.php'; ?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?php echo htmlspecialchars(SITE_NAME); ?></title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
  <h1><?php echo htmlspecialchars(SITE_NAME); ?></h1>
  <nav>
    <a href="projects.php">Mawazo</a>
    <?php if(is_admin_logged_in()): ?>
      | <a href="admin_comments.php">Admin Comments</a> | <a href="logout.php">Logout</a>
    <?php elseif(is_logged_in()): ?>
      | <span><?php echo htmlspecialchars($_SESSION['user']['name']); ?> (<?php echo htmlspecialchars($_SESSION['user']['role'] ?? 'viewer'); ?>)</span>
      | <a href="logout.php">Logout</a>
    <?php else: ?>
      | <a href="login.php">Login</a> | <a href="register.php">Register</a> | <a href="admin.php">Admin Login</a>
    <?php endif; ?>
  </nav>
  <hr>
</header>
