<?php
require '../config.php';
require_role('faculty');
auto_archive($conn);
$me = (int)$_SESSION['user']['id'];
$msg = '';

// Classes this faculty handles
$st = $conn->prepare("SELECT fc.id, fc.course_id, fc.section, c.code FROM faculty_classes fc JOIN courses c ON c.id=fc.course_id WHERE fc.faculty_id=?");
$st->bind_param('i', $me); $st->execute();
$classes = $st->get_result()->fetch_all(MYSQLI_ASSOC);

// Categories faculty may use (class schedule / quiz / class announcement)
$cats = $conn->query("SELECT * FROM categories WHERE for_faculty=1")->fetch_all(MYSQLI_ASSOC);
$allowedCats = array_column($cats, 'id');

// DELETE (own posts only)
if (isset($_GET['delete'])) {
  $id = (int)$_GET['delete'];
  $st = $conn->prepare("DELETE FROM posts WHERE id=? AND author_id=?"); $st->bind_param('ii', $id, $me); $st->execute();
  header('Location: dashboard.php'); exit;
}

// CREATE / UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id    = (int)($_POST['id'] ?? 0);
  $title = trim($_POST['title'] ?? '');
  $body  = trim($_POST['body'] ?? '');
  $cat   = (int)$_POST['category_id'];
  $cls   = (int)$_POST['class_id'];
  $exp   = $_POST['expires_at'] ? str_replace('T', ' ', $_POST['expires_at']) . ':00' : null;
  $status= ($exp && strtotime($exp) < time()) ? 'archived' : 'active';

  // server-side checks: allowed category + one of MY classes
  $class = null;
  foreach ($classes as $c) if ($c['id'] == $cls) $class = $c;

  if ($title === '' || $body === '') $msg = 'Title and message are required.';
  elseif (!in_array($cat, $allowedCats)) $msg = 'Faculty can only post class schedules, quizzes and class announcements.';
  elseif (!$class) $msg = 'Choose one of your assigned classes.';
  else {
    if ($id) {
      $st = $conn->prepare("UPDATE posts SET title=?,body=?,category_id=?,course_id=?,section=?,expires_at=?,status=? WHERE id=? AND author_id=?");
      $st->bind_param('ssiisssii', $title,$body,$cat,$class['course_id'],$class['section'],$exp,$status,$id,$me);
    } else {
      $st = $conn->prepare("INSERT INTO posts (title,body,category_id,course_id,section,author_id,expires_at,status) VALUES (?,?,?,?,?,?,?,?)");
      $st->bind_param('ssiisiss', $title,$body,$cat,$class['course_id'],$class['section'],$me,$exp,$status);
    }
    $st->execute();
    header('Location: dashboard.php'); exit;
  }
}

$edit = null;
if (isset($_GET['edit'])) {
  $eid = (int)$_GET['edit'];
  $st = $conn->prepare("SELECT * FROM posts WHERE id=? AND author_id=?"); $st->bind_param('ii', $eid, $me);
  $st->execute(); $edit = $st->get_result()->fetch_assoc();
}
$showForm = $edit || ($_GET['action'] ?? '') === 'new' || $msg;
$v = fn($k) => e($edit[$k] ?? ($_POST[$k] ?? ''));

$st = $conn->prepare("SELECT p.*, c.name AS category, co.code AS course FROM posts p
  LEFT JOIN categories c ON c.id=p.category_id LEFT JOIN courses co ON co.id=p.course_id
  WHERE p.author_id=? ORDER BY p.created_at DESC");
$st->bind_param('i', $me); $st->execute(); $posts = $st->get_result();
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Faculty Dashboard</title><link rel="stylesheet" href="../assets/style.css"></head>
<body class="layout">
<nav class="sidebar"><h2>RemindMe</h2><a href="dashboard.php">My Posts</a>
<a href="../logout.php" class="logout">Logout (<?= e($_SESSION['user']['name']) ?>)</a></nav>
<main>
  <div class="row"><h1>Faculty Dashboard</h1>
    <?php if ($classes): ?><a class="btn" href="?action=new">+ New Post</a><?php endif; ?></div>
  <p class="muted">You can post class schedules, quizzes and announcements to your own class only.</p>
  <?php if (!$classes): ?><div class="alert">No class assigned yet. Ask the admin to assign your class.</div><?php endif; ?>
  <?php if ($msg): ?><div class="alert"><?= e($msg) ?></div><?php endif; ?>

  <?php if ($showForm && $classes): ?>
  <form class="panel" method="post">
    <h2><?= $edit ? 'Edit Post' : 'New Post' ?></h2>
    <input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
    <label>Title</label><input name="title" value="<?= $v('title') ?>" required>
    <label>Message</label><textarea name="body" rows="5" required><?= $v('body') ?></textarea>
    <div class="grid">
      <div><label>Type</label><select name="category_id">
        <?php foreach ($cats as $c): ?><option value="<?= $c['id'] ?>" <?= ($edit['category_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select></div>
      <div><label>Class</label><select name="class_id">
        <?php foreach ($classes as $c):
          $sel = $edit && $edit['course_id'] == $c['course_id'] && $edit['section'] == $c['section']; ?>
          <option value="<?= $c['id'] ?>" <?= $sel ? 'selected' : '' ?>><?= e($c['code']) ?> - <?= e($c['section']) ?></option><?php endforeach; ?></select></div>
      <div><label>Expires at (optional)</label>
        <input type="datetime-local" name="expires_at" value="<?= !empty($edit['expires_at']) ? date('Y-m-d\TH:i', strtotime($edit['expires_at'])) : '' ?>"></div>
    </div>
    <button class="btn" type="submit"><?= $edit ? 'Save changes' : 'Publish' ?></button>
    <a href="dashboard.php" class="btn ghost">Cancel</a>
  </form>
  <?php endif; ?>

  <div class="panel"><table>
    <tr><th>Title</th><th>Type</th><th>Class</th><th>Expires</th><th>Status</th><th></th></tr>
    <?php while ($p = $posts->fetch_assoc()): ?>
    <tr><td><?= e($p['title']) ?></td><td><?= e($p['category'] ?? '-') ?></td>
      <td><?= e($p['course'] ?? '-') ?> - <?= e($p['section'] ?? '') ?></td><td><?= e($p['expires_at'] ?? '-') ?></td>
      <td><span class="tag <?= $p['status'] ?>"><?= e($p['status']) ?></span></td>
      <td><a href="?edit=<?= $p['id'] ?>">Edit</a> | <a href="?delete=<?= $p['id'] ?>" onclick="return confirm('Delete this post?')">Delete</a></td></tr>
    <?php endwhile; ?>
  </table></div>
</main></body></html>
