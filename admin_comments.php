<?php
require_once __DIR__ . '/config.php';
if (!is_admin_logged_in()) {
    header('Location: admin.php');
    exit;
}
$stmt = $pdo->prepare('SELECT o.*, p.title as project_title, COALESCE(u.name, "Anonymous") AS author FROM opinions o LEFT JOIN users u ON o.user_id = u.id JOIN projects p ON o.project_id = p.id ORDER BY o.created_at DESC');
$stmt->execute();
$comments = $stmt->fetchAll();
?>
<?php include 'header.php'; ?>
<h2>Admin - Maoni yote</h2>
<p>Unaweza kuona maoni ya watu wote hapa.</p>
<?php foreach ($comments as $comment): ?>
  <div class="project">
    <div class="meta"><?php echo htmlspecialchars($comment['project_title']); ?> · <?php echo htmlspecialchars($comment['author']); ?> · <?php echo $comment['created_at']; ?></div>
    <p><strong><?php echo htmlspecialchars(ucfirst($comment['type'])); ?>:</strong> <?php echo nl2br(htmlspecialchars($comment['content'])); ?></p>
  </div>
<?php endforeach; ?>
<?php include 'footer.php'; ?>
