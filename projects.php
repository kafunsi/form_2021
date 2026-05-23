<?php
require_once __DIR__ . '/config.php';
// create project
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!is_logged_in()) { $error = 'Please login to submit an idea.'; }
    else if ($_SESSION['user']['role'] !== 'submitter') { $error = 'Only submitter users can add new ideas.'; }
    else {
        $title = trim($_POST['title'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        if ($title === '') { $error = 'Please enter your idea.'; }
        else {
            $stmt = $pdo->prepare('INSERT INTO projects (title,description,creator_id) VALUES (?,?,?)');
            $stmt->execute([$title,$desc,$_SESSION['user']['id']]);
            header('Location: projects.php'); exit;
        }
    }
}

$projects = $pdo->query('SELECT p.*, u.name as creator FROM projects p JOIN users u ON p.creator_id = u.id ORDER BY p.created_at DESC')->fetchAll();
?>
<?php include 'header.php'; ?>
<h2>Mawazo</h2>
<?php if($error): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

<?php if(is_logged_in() && get_user_role() === 'submitter'): ?>
<h3>Toa Wazo</h3>
<form method="post">
  <label>Wazo</label>
  <input name="title" required>
  <label>Kwanini umependa</label>
  <textarea name="description"></textarea>
  <button type="submit">Submit</button>
</form>
<?php elseif(is_logged_in()): ?>
<p>Wewe ni mtazamaji. Unaweza kuona mawazo ya wengine hapa chini.</p>
<?php else: ?>
<p><a href="login.php">Login</a> to submit or view ideas.</p>
<?php endif; ?>

<h3>Mawazo yote</h3>
<?php foreach($projects as $p): ?>
  <div class="project">
    <h4><a href="project.php?id=<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['title']); ?></a></h4>
    <div class="meta">By <?php echo htmlspecialchars($p['creator']); ?> · <?php echo $p['created_at']; ?></div>
    <p><?php echo nl2br(htmlspecialchars($p['description'])); ?></p>
  </div>
<?php endforeach; ?>

<?php include 'footer.php'; ?>
