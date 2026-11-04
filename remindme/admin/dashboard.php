<?php
require '../config.php';
require_role('admin');
auto_archive($conn);

$one = fn($sql) => (int)$conn->query($sql)->fetch_row()[0];
$stats = [
  ['Active Posts',     $one("SELECT COUNT(*) FROM posts WHERE status='active'"),   'ok'],
  ['Archived Posts',   $one("SELECT COUNT(*) FROM posts WHERE status='archived'"), 'muted'],
  ['Expiring in 7 days', $one("SELECT COUNT(*) FROM posts WHERE status='active' AND expires_at BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY)"), 'warn'],
  ['Students',         $one("SELECT COUNT(*) FROM users WHERE role='student'"),    'info'],
  ['Faculty',          $one("SELECT COUNT(*) FROM users WHERE role='faculty'"),    'info'],
];

// Active posts per category (for the bar chart)
$cats = $conn->query("SELECT c.name, COUNT(p.id) AS total FROM categories c
  LEFT JOIN posts p ON p.category_id=c.id AND p.status='active'
  GROUP BY c.id ORDER BY total DESC, c.name")->fetch_all(MYSQLI_ASSOC);
$maxCat = max([1, ...array_map('intval', array_column($cats, 'total'))]);

// Posts that will expire soon
$expiring = $conn->query("SELECT title, expires_at FROM posts
  WHERE status='active' AND expires_at BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY)
  ORDER BY expires_at LIMIT 5")->fetch_all(MYSQLI_ASSOC);

// Recent posts
$recent = $conn->query("SELECT p.*, c.name AS category, co.code AS course, u.name AS author
  FROM posts p LEFT JOIN categories c ON c.id=p.category_id
  LEFT JOIN courses co ON co.id=p.course_id JOIN users u ON u.id=p.author_id
  ORDER BY p.created_at DESC LIMIT 8")->fetch_all(MYSQLI_ASSOC);

$fmt = fn($d) => date('M j, Y g:i A', strtotime($d));
?>
<!DOCTYPE html>
<html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Dashboard - RemindMe</title>
<link rel="stylesheet" href="../assets/style.css">
<link rel="stylesheet" href="../assets/admin.css">
</head>
<body class="layout">
<?php include '../includes/admin_nav.php'; ?>
<main>
  <header class="topbar">
    <div>
      <h1>Admin Dashboard</h1>
      <p class="muted">Welcome, <?= e($_SESSION['user']['name']) ?> &bull; <?= date('l, F j, Y') ?></p>
    </div>
    <a class="btn" href="posts.php?action=new">+ New Post</a>
  </header>

  <section class="stats">
    <?php foreach ($stats as [$label, $n, $tone]): ?>
      <div class="stat <?= $tone ?>"><span class="num"><?= $n ?></span><span><?= e($label) ?></span></div>
    <?php endforeach; ?>
  </section>

  <section class="two-col">
    <div class="panel">
      <h2>Active posts by category</h2>
      <?php foreach ($cats as $c): ?>
        <div class="bar-row">
          <span class="bar-label"><?= e($c['name']) ?></span>
          <div class="bar"><i data-width="<?= round($c['total'] / $maxCat * 100) ?>"></i></div>
          <b><?= (int)$c['total'] ?></b>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="panel">
      <h2>Expiring soon</h2>
      <?php if (!$expiring): ?><p class="muted">No posts expire in the next 7 days.</p><?php endif; ?>
      <ul class="plain">
        <?php foreach ($expiring as $x): ?>
          <li><?= e($x['title']) ?><small class="muted"><?= e($fmt($x['expires_at'])) ?></small></li>
        <?php endforeach; ?>
      </ul>
      <h2 class="mt">Quick actions</h2>
      <div class="actions">
        <a class="btn ghost" href="posts.php?action=new">New post</a>
        <a class="btn ghost" href="users.php">Add user</a>
        <a class="btn ghost" href="classes.php">Assign class</a>
      </div>
    </div>
  </section>

  <section class="panel">
    <div class="row">
      <h2>Recent posts</h2>
      <div class="tools">
        <input type="search" id="search" placeholder="Search title, author...">
        <div class="seg" id="statusFilter">
          <button type="button" data-status="all" class="on">All</button>
          <button type="button" data-status="active">Active</button>
          <button type="button" data-status="archived">Archived</button>
        </div>
      </div>
    </div>
    <table id="postsTable">
      <tr><th>Title</th><th>Category</th><th>Audience</th><th>Author</th><th>Status</th><th>Posted</th></tr>
      <?php foreach ($recent as $p): ?>
      <tr data-status="<?= e($p['status']) ?>">
        <td><?= e($p['title']) ?></td>
        <td><?= e($p['category'] ?? '-') ?></td>
        <td><?= e($p['course'] ?? 'All courses') ?><?= $p['section'] ? ' - ' . e($p['section']) : '' ?></td>
        <td><?= e($p['author']) ?></td>
        <td><span class="tag <?= e($p['status']) ?>"><?= e($p['status']) ?></span></td>
        <td><?= e($fmt($p['created_at'])) ?></td>
      </tr>
      <?php endforeach; ?>
    </table>
    <?php if (!$recent): ?><p class="muted">No posts yet. Click "+ New Post" to create the first one.</p><?php endif; ?>
    <p class="muted" id="noMatch" hidden>No posts match your search.</p>
    <a href="posts.php">View all posts &rarr;</a>
  </section>
</main>
<script src="../assets/admin.js"></script>
</body></html>
