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
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title>form_2021 Ruchugi Secondary | Mawazo Ideas</title>
  <!-- Google Fonts & simple reset styling -->
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      background: linear-gradient(145deg, #f6f9fc 0%, #eef2f5 100%);
      font-family: 'Segoe UI', 'Inter', system-ui, -apple-system, 'Roboto', 'Helvetica Neue', sans-serif;
      color: #1a2c3e;
      line-height: 1.5;
      padding: 2rem 1.5rem;
      min-height: 100vh;
    }

    /* main container for consistent width */
    body > * {
      max-width: 1280px;
      margin-left: auto;
      margin-right: auto;
    }

    /* header area */
    header {
      margin-bottom: 2rem;
      animation: fadeSlideDown 0.5s ease-out;
    }

    h1 {
      font-size: 2.4rem;
      font-weight: 700;
      background: linear-gradient(135deg, #1e4668, #2b5a8c);
      background-clip: text;
      -webkit-background-clip: text;
      color: transparent;
      letter-spacing: -0.3px;
      margin-bottom: 0.5rem;
      text-shadow: 2px 2px 6px rgba(0,0,0,0.02);
      border-left: 5px solid #ffb347;
      padding-left: 1.2rem;
    }

    nav {
      background: rgba(255,255,255,0.7);
      backdrop-filter: blur(4px);
      padding: 0.75rem 1.2rem;
      border-radius: 60px;
      display: inline-flex;
      flex-wrap: wrap;
      gap: 0.6rem 1rem;
      margin-top: 0.75rem;
      box-shadow: 0 2px 6px rgba(0,0,0,0.03);
      font-weight: 500;
    }

    nav a {
      color: #1f5e8c;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.2s ease;
      padding: 0.2rem 0.3rem;
      border-radius: 30px;
    }

    nav a:hover {
      color: #e67e22;
      background-color: rgba(230,126,34,0.08);
      transform: translateY(-1px);
    }

    hr {
      margin: 0.8rem 0 1rem 0;
      border: none;
      height: 2px;
      background: linear-gradient(90deg, #cbdbe0, #e2e8f0, #cbdbe0);
      border-radius: 4px;
    }

    /* main content boxes */
    h2 {
      font-size: 2.2rem;
      font-weight: 700;
      margin: 2rem 0 0.75rem 0;
      color: #1e3a5f;
      display: inline-block;
      background: linear-gradient(120deg, #f9f3e3, transparent);
      padding: 0.2rem 1rem 0.2rem 0.8rem;
      border-radius: 0 40px 40px 0;
    }

    h3 {
      font-size: 1.6rem;
      font-weight: 600;
      margin: 1.8rem 0 1rem 0;
      color: #2c4e6e;
      border-bottom: 3px dotted #ffb347;
      display: inline-block;
      padding-bottom: 0.2rem;
    }

    /* card & container for mawazo */
    .idea-card {
      background: #ffffffdd;
      backdrop-filter: blur(2px);
      background: white;
      border-radius: 32px;
      padding: 1.8rem 2rem;
      margin: 1.5rem 0;
      box-shadow: 0 20px 35px -12px rgba(0, 0, 0, 0.1);
      transition: all 0.25s ease;
      border: 1px solid rgba(255,255,240,0.8);
    }

    .idea-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 28px 36px -14px rgba(0, 0, 0, 0.15);
      border-color: #ffd8ae;
    }

    .cta-message {
      background: linear-gradient(115deg, #ffe9d4, #fff6ed);
      border-left: 8px solid #ffb347;
      border-radius: 24px;
      padding: 1.2rem 2rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 1rem;
      margin: 0.5rem 0 1.5rem 0;
    }

    .cta-message p {
      margin: 0;
      font-size: 1.2rem;
      font-weight: 500;
      color: #a5561f;
    }

    .btn-login {
      background: #ff9f4a;
      border: none;
      padding: 0.65rem 1.6rem;
      border-radius: 40px;
      font-weight: 700;
      font-size: 1rem;
      color: white;
      text-decoration: none;
      display: inline-block;
      transition: 0.2s;
      box-shadow: 0 4px 8px rgba(0,0,0,0.05);
    }

    .btn-login:hover {
      background: #e67e22;
      transform: scale(0.97);
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }

    /* idea list mockup */
    .ideas-grid {
      display: flex;
      flex-direction: column;
      gap: 1.2rem;
      margin: 1rem 0 2rem;
    }

    .idea-item {
      background: white;
      border-radius: 28px;
      padding: 1.2rem 1.6rem;
      transition: all 0.2s;
      border: 1px solid #e9edf2;
      box-shadow: 0 2px 8px rgba(0,0,0,0.02);
      display: flex;
      align-items: flex-start;
      gap: 1rem;
      flex-wrap: wrap;
    }

    .idea-emoji {
      font-size: 2.2rem;
      background: #fef4e8;
      width: 56px;
      height: 56px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 60px;
    }

    .idea-content {
      flex: 1;
    }

    .idea-title {
      font-weight: 800;
      font-size: 1.25rem;
      color: #1f4662;
      margin-bottom: 0.3rem;
    }

    .idea-desc {
      color: #3e5a6c;
      font-size: 0.95rem;
      margin-top: 0.25rem;
    }

    .idea-meta {
      font-size: 0.75rem;
      color: #8aa0b0;
      margin-top: 0.5rem;
      display: flex;
      gap: 1rem;
    }

    /* footer */
    footer {
      margin-top: 3.5rem;
    }

    footer hr {
      background: linear-gradient(90deg, #cddae9, #ffffff, #cddae9);
      height: 1px;
    }

    footer p {
      text-align: center;
      font-size: 0.85rem;
      color: #4b6f8c;
      padding: 1rem 0.5rem;
      font-weight: 500;
    }

    /* animations */
    @keyframes fadeSlideDown {
      0% {
        opacity: 0;
        transform: translateY(-20px);
      }
      100% {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* responsiveness */
    @media (max-width: 680px) {
      body {
        padding: 1rem;
      }
      h1 {
        font-size: 1.8rem;
        padding-left: 0.8rem;
      }
      h2 {
        font-size: 1.7rem;
      }
      .cta-message {
        flex-direction: column;
        align-items: flex-start;
      }
      nav {
        flex-wrap: wrap;
        padding: 0.6rem 1rem;
      }
      .idea-emoji {
        width: 44px;
        height: 44px;
        font-size: 1.7rem;
      }
    }

    /* subtle decoration */
    .insight-badge {
      display: inline-block;
      background: #eef3fc;
      padding: 0.25rem 0.8rem;
      border-radius: 30px;
      font-size: 0.75rem;
      font-weight: 600;
      color: #2c628b;
      margin-right: 0.5rem;
    }
  </style>
</head>
<body>

<header>
  <h1>📚 form_2021 · Ruchugi Secondary</h1>
  <nav>
    <a href="projects.php">🌱 Mawazo</a>
    <span style="color:#cbd5e1;">|</span>
    <a href="login.php">🔐 Login</a>
    <a href="register.php">📝 Register</a>
    <a href="admin.php">⚙️ Admin Login</a>
  </nav>
  <hr>
</header>

<main>
  <!-- welcome & inspiration section -->
  <div class="idea-card">
    <div style="display: flex; gap: 0.6rem; align-items: center; flex-wrap: wrap; margin-bottom: 0.5rem;">
      <span class="insight-badge">✨ Student Corner</span>
      <span class="insight-badge">💡 Innovation Hub</span>
    </div>
    <p style="font-size: 1.1rem; margin-top: 0.3rem; color: #2c4f6e;"><strong>Karibu Ruchugi!</strong> Share visionary ideas, class projects, and community solutions. Every great transformation starts with a single <strong>Mawazo</strong> 💭</p>
  </div>

  <h2>🌟 Mawazo hub</h2>
  <div class="cta-message">
    <p>📢 Have a brilliant idea? Share it with the community!</p>
    <a href="login.php" class="btn-login">✨ Login to submit →</a>
  </div>

  <h3>📌 Mawazo yote — trending concepts</h3>
  
  <!-- dynamic ideas showcase with creative mock data reflecting real ideas from secondary students -->
  <div class="ideas-grid">
    <div class="idea-item">
      <div class="idea-emoji">🌍</div>
      <div class="idea-content">
        <div class="idea-title">Green corner: school recycling initiative</div>
        <div class="idea-desc">A student-led project to reduce plastic waste around Ruchugi compound. Build eco-bricks and monthly collection points.</div>
        <div class="idea-meta">👥 by Form 3A team | ❤️ 24 reactions | 🗓️ 2 days ago</div>
      </div>
    </div>
    <div class="idea-item">
      <div class="idea-emoji">🤖</div>
      <div class="idea-content">
        <div class="idea-title">STEM Club: Low-cost water pump prototype</div>
        <div class="idea-desc">Using recycled materials and simple mechanics to help local farms. Call for volunteers and workshop this term.</div>
        <div class="idea-meta">👥 by Innovators Group | ❤️ 18 reactions | 🗓️ last week</div>
      </div>
    </div>
    <div class="idea-item">
      <div class="idea-emoji">📖</div>
      <div class="idea-content">
        <div class="idea-title">Digital library access (offline archive)</div>
        <div class="idea-desc">Collect e-books, notes, and past papers on a local server. Reduce cost of learning materials for all forms.</div>
        <div class="idea-meta">👥 by Mawazo Crew | ❤️ 37 reactions | 🗓️ 5 days ago</div>
      </div>
    </div>
    <div class="idea-item">
      <div class="idea-emoji">🎨</div>
      <div class="idea-content">
        <div class="idea-title">Talent showcase — art & culture festival</div>
        <div class="idea-desc">Annual event to celebrate music, poetry, drama, and traditional dances. Strengthen unity and creativity.</div>
        <div class="idea-meta">👥 by Culture Committee | ❤️ 42 reactions | 🗓️ 3 days ago</div>
      </div>
    </div>
    <div class="idea-item">
      <div class="idea-emoji">⚡</div>
      <div class="idea-content">
        <div class="idea-title">Tech up: coding workshop for beginners</div>
        <div class="idea-desc">Introduce HTML/CSS, problem-solving, and build mini web projects. Saturday classes available.</div>
        <div class="idea-meta">👥 by CS club | ❤️ 31 reactions | 🗓️ 1 week ago</div>
      </div>
    </div>
  </div>

  <!-- subtle note: login to submit new idea -->
  <div style="background: #eef2fa; border-radius: 2rem; padding: 0.8rem 1.5rem; margin-top: 0.5rem; text-align: center; font-size: 0.9rem;">
    💡 <strong>Be part of the change</strong> — <a href="login.php" style="color:#e67e22; font-weight:600;">Login now</a> to post your own Mawazo, comment, or vote for best ideas.
  </div>
</main>

<footer>
  <hr>
  <p>© form_2021 Ruchugi Secondary — fostering innovation, community, and future leaders.</p>
</footer>

<!-- optional small hover interaction to make the login call-out smoother -->
</body>
</html>
