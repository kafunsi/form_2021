<?php
require_once __DIR__ . '/config.php';
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: projects.php'); exit; }

// handle new opinion/planning
$success = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = ($_POST['type'] === 'planning') ? 'planning' : 'opinion';
    $content = trim($_POST['content'] ?? '');
    if ($content !== '') {
        $userId = is_logged_in() ? $_SESSION['user']['id'] : null;
        $stmt = $pdo->prepare('INSERT INTO opinions (project_id,user_id,type,content) VALUES (?,?,?,?)');
        $stmt->execute([$id,$userId,$type,$content]);
        $success = 'Your comment has been posted successfully!';
        // Refresh to show new comment
        header('Refresh: 2; url=project.php?id=' . $id);
    } else {
        $error = 'Please enter your comment before submitting.';
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

<style>
    /* Project Detail Styles */
    .project-detail {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 20px;
        padding: 30px;
        margin-bottom: 40px;
        color: white;
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    }
    
    .project-detail h2 {
        font-size: 2.5rem;
        margin-bottom: 15px;
        font-weight: 700;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
    }
    
    .project-detail .meta {
        color: rgba(255,255,255,0.9);
        font-size: 0.95rem;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid rgba(255,255,255,0.2);
    }
    
    .project-detail p {
        font-size: 1.1rem;
        line-height: 1.6;
        color: rgba(255,255,255,0.95);
    }
    
    /* Comment Form Styles */
    .comment-form-container {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        border-radius: 20px;
        padding: 30px;
        margin-bottom: 40px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    
    .comment-form-container h3 {
        color: #2c3e50;
        font-size: 1.8rem;
        margin-bottom: 20px;
        font-weight: 600;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: #2c3e50;
        font-weight: 600;
        font-size: 1rem;
    }
    
    .form-group select {
        width: 100%;
        padding: 12px;
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: white;
    }
    
    .form-group select:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
    }
    
    .form-group textarea {
        width: 100%;
        padding: 12px;
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        font-size: 1rem;
        min-height: 120px;
        transition: all 0.3s ease;
        font-family: inherit;
    }
    
    .form-group textarea:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
    }
    
    .btn-submit {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 12px 30px;
        border-radius: 10px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        width: 100%;
    }
    
    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(102,126,234,0.3);
    }
    
    .info-note {
        background: rgba(102,126,234,0.1);
        padding: 12px;
        border-radius: 10px;
        text-align: center;
        margin-top: 15px;
        color: #667eea;
        font-size: 0.9rem;
    }
    
    /* Comments Section */
    .comments-section {
        margin-top: 40px;
    }
    
    .comments-section h3 {
        color: #2c3e50;
        font-size: 1.8rem;
        margin-bottom: 20px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .comments-count {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 1rem;
    }
    
    .comment-card {
        background: white;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        border-left: 4px solid #667eea;
    }
    
    .comment-card:hover {
        transform: translateX(5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    
    .comment-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        flex-wrap: wrap;
        gap: 10px;
    }
    
    .comment-type {
        display: inline-block;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .comment-type.opinion {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        color: white;
    }
    
    .comment-type.planning {
        background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        color: white;
    }
    
    .comment-author {
        color: #667eea;
        font-weight: 600;
        font-size: 0.95rem;
    }
    
    .comment-date {
        color: #7f8c8d;
        font-size: 0.85rem;
    }
    
    .comment-content {
        color: #34495e;
        line-height: 1.6;
        font-size: 1rem;
    }
    
    /* Alert Messages */
    .alert {
        padding: 15px 20px;
        border-radius: 10px;
        margin-bottom: 20px;
        font-weight: 500;
    }
    
    .alert-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .alert-error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    /* Empty State */
    .empty-comments {
        text-align: center;
        padding: 60px 20px;
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        border-radius: 15px;
        color: #666;
    }
    
    .empty-comments p {
        font-size: 1.1rem;
        margin: 10px 0;
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
        .project-detail {
            padding: 20px;
        }
        
        .project-detail h2 {
            font-size: 1.8rem;
        }
        
        .comment-form-container {
            padding: 20px;
        }
        
        .comment-header {
            flex-direction: column;
            align-items: flex-start;
        }
    }
    
    /* Animation */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .comment-card {
        animation: fadeIn 0.5s ease-out;
    }
</style>

<main>
    <?php if($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <?php if($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="project-detail">
        <h2><?php echo htmlspecialchars($project['title']); ?></h2>
        <div class="meta">
            <strong>👤 By <?php echo htmlspecialchars($project['creator']); ?></strong> · 
            📅 <?php echo date('F j, Y, g:i a', strtotime($project['created_at'])); ?>
        </div>
        <p><?php echo nl2br(htmlspecialchars($project['description'])); ?></p>
    </div>

    <div class="comment-form-container">
        <h3>💬 Share Your Thoughts</h3>
        <form method="post">
            <div class="form-group">
                <label>📝 Comment Type</label>
                <select name="type">
                    <option value="opinion">💡 Opinion - Share your thoughts and feedback</option>
                    <option value="planning">📋 Planning - Suggest improvements or plans</option>
                </select>
            </div>
            <div class="form-group">
                <label>✍️ Your Message</label>
                <textarea name="content" required placeholder="Write your comment here..."></textarea>
            </div>
            <button type="submit" class="btn-submit">🚀 Post Comment</button>
        </form>
        <div class="info-note">
            💡 You can post comments without logging in. Your voice matters!
        </div>
    </div>

    <div class="comments-section">
        <h3>
            💬 Community Discussion 
            <span class="comments-count"><?php echo count($opinions); ?> comments</span>
        </h3>
        
        <?php if(count($opinions) === 0): ?>
            <div class="empty-comments">
                <p>🌟 No comments yet</p>
                <p>Be the first to share your thoughts on this project!</p>
            </div>
        <?php else: ?>
            <?php foreach($opinions as $o): ?>
                <div class="comment-card">
                    <div class="comment-header">
                        <div>
                            <span class="comment-type <?php echo $o['type']; ?>">
                                <?php echo $o['type'] === 'opinion' ? '💡 Opinion' : '📋 Planning'; ?>
                            </span>
                        </div>
                        <div>
                            <span class="comment-author">👤 <?php echo htmlspecialchars($o['author']); ?></span>
                            <span class="comment-date">• <?php echo date('M d, Y', strtotime($o['created_at'])); ?></span>
                        </div>
                    </div>
                    <div class="comment-content">
                        <?php echo nl2br(htmlspecialchars($o['content'])); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<?php include 'footer.php'; ?>