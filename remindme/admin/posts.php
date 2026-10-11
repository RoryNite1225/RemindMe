<?php
require '../config.php';
require_role('admin');
auto_archive($conn);
$msg = '';

if (isset($_GET['delete'])) {
  $id = (int)$_GET['delete'];
  $st = $conn->prepare("DELETE FROM posts WHERE id=?"); $st->bind_param('i', $id); $st->execute();
  header('Location: posts.php'); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id    = (int)($_POST['id'] ?? 0);
  $title = trim($_POST['title'] ?? '');
  $body  = trim($_POST['body'] ?? '');
  $cat   = (int)$_POST['category_id'] ?: null;
  $course= (int)$_POST['course_id'] ?: null;
  $sec   = trim($_POST['section'] ?? '') ?: null;
  $exp   = $_POST['expires_at'] ? str_replace('T', ' ', $_POST['expires_at']) . ':00' : null;
  $status= ($exp && strtotime($exp) < time()) ? 'archived' : 'active';

  if ($title === '' || $body === '') {
    $msg = 'Title and message are required.';
  } elseif ($id) {
    $st = $conn->prepare("UPDATE posts SET title=?,body=?,category_id=?,course_id=?,section=?,expires_at=?,status=? WHERE id=?");
    $st->bind_param('ssiisssi', $title,$body,$cat,$course,$sec,$exp,$status,$id); $st->execute();
    header('Location: posts.php'); exit;
  } else {
    $aid = $_SESSION['user']['id'];
    $st = $conn->prepare("INSERT INTO posts (title,body,category_id,course_id,section,author_id,expires_at,status) VALUES (?,?,?,?,?,?,?,?)");
    $st->bind_param('ssiisiss', $title,$body,$cat,$course,$sec,$aid,$exp,$status); $st->execute();
    header('Location: posts.php'); exit;
  }
}

$edit = null;
if (isset($_GET['edit'])) {
  $st = $conn->prepare("SELECT * FROM posts WHERE id=?"); $eid = (int)$_GET['edit'];
  $st->bind_param('i', $eid); $st->execute(); $edit = $st->get_result()->fetch_assoc();
}
$showForm = $edit || ($_GET['action'] ?? '') === 'new' || $msg;

$cats    = $conn->query("SELECT * FROM categories")->fetch_all(MYSQLI_ASSOC);
$courses = $conn->query("SELECT * FROM courses")->fetch_all(MYSQLI_ASSOC);

$where = ['1=1']; $params = []; $types = '';
if (!empty($_GET['f_course']))   { $where[] = 'p.course_id=?';   $params[] = (int)$_GET['f_course'];   $types .= 'i'; }
if (!empty($_GET['f_category'])) { $where[] = 'p.category_id=?'; $params[] = (int)$_GET['f_category']; $types .= 'i'; }
if (!empty($_GET['f_status']))   { $where[] = 'p.status=?';      $params[] = $_GET['f_status'];        $types .= 's'; }
$sql = "SELECT p.*, c.name AS category, co.code AS course, u.name AS author FROM posts p
  LEFT JOIN categories c ON c.id=p.category_id LEFT JOIN courses co ON co.id=p.course_id
  JOIN users u ON u.id=p.author_id WHERE " . implode(' AND ', $where) . " ORDER BY p.created_at DESC";
$st = $conn->prepare($sql);
if ($params) $st->bind_param($types, ...$params);
$st->execute(); $posts = $st->get_result();

$v = fn($k) => e($edit[$k] ?? ($_POST[$k] ?? ''));
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Manage Posts</title><link rel="stylesheet" href="../assets/style.css"></head>
<body class="layout">
<?php include '../includes/admin_nav.php'; ?>
<main>
  <div class="row"><h1>Manage Posts</h1><a class="btn" href="?action=new">+ New Post</a></div>
  <?php if ($msg): ?><div class="alert"><?= e($msg) ?></div><?php endif; ?>

  <?php if ($showForm): ?>
  <form class="panel" method="post">
    <h2><?= $edit ? 'Edit Post' : 'New Post' ?></h2>
    <input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
    <label>Title</label><input name="title" value="<?= $v('title') ?>" required>
    <label>Message</label><textarea name="body" rows="5" required><?= $v('body') ?></textarea>
    <div class="grid">
      <div><label>Category</label>
        <select name="category_id"><option value="0">-</option>
        <?php foreach ($cats as $c): ?><option value="<?= $c['id'] ?>" <?= ($edit['category_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select></div>
      <div><label>Course</label>
        <select name="course_id"><option value="0">All courses</option>
        <?php foreach ($courses as $c): ?><option value="<?= $c['id'] ?>" <?= ($edit['course_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= e($c['code']) ?></option><?php endforeach; ?></select></div>
      <div><label>Section (blank = all)</label><input name="section" value="<?= $v('section') ?>"></div>
      <div><label>Expires at (optional)</label>
        <input type="datetime-local" name="expires_at" value="<?= !empty($edit['expires_at']) ? date('Y-m-d\TH:i', strtotime($edit['expires_at'])) : '' ?>"></div>
    </div>
    <button class="btn" type="submit"><?= $edit ? 'Save changes' : 'Publish' ?></button>
    <a href="posts.php" class="btn ghost">Cancel</a>
  </form>
  <?php endif; ?>

  <form class="filters" method="get">
    <select name="f_course"><option value="">All courses</option>
      <?php foreach ($courses as $c): ?><option value="<?= $c['id'] ?>" <?= ($_GET['f_course'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= e($c['code']) ?></option><?php endforeach; ?></select>
    <select name="f_category"><option value="">All categories</option>
      <?php foreach ($cats as $c): ?><option value="<?= $c['id'] ?>" <?= ($_GET['f_category'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select>
    <select name="f_status"><option value="">Any status</option>
      <option value="active" <?= ($_GET['f_status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
      <option value="archived" <?= ($_GET['f_status'] ?? '') === 'archived' ? 'selected' : '' ?>>Archived</option></select>
    <button class="btn" type="submit">Filter</button>
  </form>

  <div class="panel">
    <table>
      <tr><th>Title</th><th>Category</th><th>Course/Sec</th><th>Author</th><th>Expires</th><th>Status</th><th></th></tr>
      <?php while ($p = $posts->fetch_assoc()): ?>
      <tr>
        <td><?= e($p['title']) ?></td><td><?= e($p['category'] ?? '-') ?></td>
        <td><?= e($p['course'] ?? 'All') ?> / <?= e($p['section'] ?? 'All') ?></td>
        <td><?= e($p['author']) ?></td><td><?= e($p['expires_at'] ?? '-') ?></td>
        <td><span class="tag <?= $p['status'] ?>"><?= e($p['status']) ?></span></td>
        <td><a href="?edit=<?= $p['id'] ?>">Edit</a> |
            <a href="?delete=<?= $p['id'] ?>" onclick="return confirm('Delete this post?')">Delete</a></td>
      </tr>
      <?php endwhile; ?>
    </table>
  </div>
</main></body></html>
