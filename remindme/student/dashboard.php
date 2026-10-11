<?php
require '../config.php';
require_role('student');
auto_archive($conn);
$me  = (int)$_SESSION['user']['id'];
$cid = (int)$_SESSION['user']['course_id'];
$sec = (string)$_SESSION['user']['section'];

// Mark everything currently visible as read when the bell is clicked
if (isset($_GET['read'])) {
  $conn->query("INSERT IGNORE INTO post_reads (user_id, post_id)
    SELECT $me, id FROM posts WHERE status='active'
    AND (course_id IS NULL OR course_id=$cid) AND (section IS NULL OR section='" . $conn->real_escape_string($sec) . "')");
  header('Location: dashboard.php'); exit;
}

$view = ($_GET['view'] ?? 'active') === 'archived' ? 'archived' : 'active';
$cats = $conn->query("SELECT * FROM categories")->fetch_all(MYSQLI_ASSOC);

$sql = "SELECT p.*, c.name AS category, u.name AS author, u.role AS author_role,
          (pr.post_id IS NOT NULL) AS is_read
        FROM posts p
        LEFT JOIN categories c ON c.id=p.category_id
        JOIN users u ON u.id=p.author_id
        LEFT JOIN post_reads pr ON pr.post_id=p.id AND pr.user_id=?
        WHERE p.status=? AND (p.course_id IS NULL OR p.course_id=?) AND (p.section IS NULL OR p.section=?)";
$types = 'isis'; $params = [$me, $view, $cid, $sec];
if (!empty($_GET['cat'])) { $sql .= " AND p.category_id=?"; $types .= 'i'; $params[] = (int)$_GET['cat']; }
$sql .= " ORDER BY p.created_at DESC";
$st = $conn->prepare($sql); $st->bind_param($types, ...$params); $st->execute();
$posts = $st->get_result()->fetch_all(MYSQLI_ASSOC);

$unread = 0;
if ($view === 'active') foreach ($posts as $p) if (!$p['is_read']) $unread++;
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Student Dashboard</title><link rel="stylesheet" href="../assets/style.css"></head>
<body class="layout">
<nav class="sidebar"><h2>RemindMe</h2>
  <a href="dashboard.php">Announcements</a>
  <a href="?view=archived">Archived</a>
  <a href="../logout.php" class="logout">Logout (<?= e($_SESSION['user']['name']) ?>)</a></nav>
<main>
  <div class="row">
    <h1><?= $view === 'archived' ? 'Archived' : 'Announcements' ?></h1>
    <a class="bell" href="?read=1" title="Mark all as read">&#128276;<?php if ($unread): ?><b><?= $unread ?></b><?php endif; ?></a>
  </div>
  <form class="filters" method="get">
    <input type="hidden" name="view" value="<?= e($view) ?>">
    <select name="cat"><option value="">All categories</option>
      <?php foreach ($cats as $c): ?><option value="<?= $c['id'] ?>" <?= ($_GET['cat'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select>
    <button class="btn" type="submit">Filter</button>
  </form>

  <?php if (!$posts): ?><div class="panel">Nothing here yet.</div><?php endif; ?>
  <?php foreach ($posts as $p): ?>
  <article class="post <?= (!$p['is_read'] && $view === 'active') ? 'unread' : '' ?>">
    <div class="row"><h3><?= e($p['title']) ?></h3><span class="tag active"><?= e($p['category'] ?? 'General') ?></span></div>
    <p><?= nl2br(e($p['body'])) ?></p>
    <small class="muted">By <?= e($p['author']) ?> (<?= e($p['author_role']) ?>) &bull; <?= e($p['created_at']) ?>
      <?php if ($p['expires_at']): ?> &bull; Until <?= e($p['expires_at']) ?><?php endif; ?></small>
  </article>
  <?php endforeach; ?>
</main></body></html>
