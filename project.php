<?php
require_once __DIR__ . '/config.php';
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: projects.php'); exit; }

// handle new opinion/planning
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = ($_POST['type'] === 'planning') ? 'planning' : 'opinion';
    $content = trim($_POST['content'] ?? '');
    if ($content !== '') {
        $userId = is_logged_in() ? $_SESSION['user']['id'] : null;
        $stmt = $pdo->prepare('INSERT INTO opinions (project_id,user_id,type,content) VALUES (?,?,?,?)');
        $stmt->execute([$id,$userId,$type,$content]);
        header('Location: project.php?id=' . $id);
        exit;
    }
}

$stmt = $pdo->prepare('SELECT p.*, u.name as creator FROM projects p JOIN users u ON p.creator_id = u.id WHERE p.id = ?');
$stmt->execute([$id]);
$project = $stmt->fetch();
if (!$project) { header('Location: projects.php'); exit; }

$opinions = $pdo->prepare('SELECT o.*, COALESCE(u.name, "Anonymous") AS author FROM opinions o LEFT JOIN users u ON o.user_id = u.id WHERE o.project_id = ? ORDER BY o.created_at DESC');
$opinions->execute([$id]);
$opinions = $opinions->fetchAll();
?>
<?php include 'header.php'; ?>
<h2><?php echo htmlspecialchars($project['title']); ?></h2>
<div class="meta">By <?php echo htmlspecialchars($project['creator']); ?> · <?php echo $project['created_at']; ?></div>
<p><?php echo nl2br(htmlspecialchars($project['description'])); ?></p>

<h3>Maoni na mipango</h3>
<form method="post">
  <label>Aina</label>
  <select name="type"><option value="opinion">Opinion</option><option value="planning">Planning</option></select>
  <label>Maoni</label>
  <textarea name="content" required></textarea>
  <button type="submit">Tuma maoni</button>
</form>
<p>Unaweza kuweka maoni bila kulogin.</p>

<?php foreach($opinions as $o): ?>
  <div class="project">
    <div class="meta"><?php echo strtoupper($o['type']); ?> by <?php echo htmlspecialchars($o['author']); ?> · <?php echo $o['created_at']; ?></div>
    <p><?php echo nl2br(htmlspecialchars($o['content'])); ?></p>
  </div>
<?php endforeach; ?>

<?php include 'footer.php'; ?>
