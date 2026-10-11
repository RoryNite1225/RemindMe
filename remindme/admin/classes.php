<?php
require '../config.php';
require_role('admin');
$msg = '';

if (isset($_GET['delete'])) {
  $id = (int)$_GET['delete'];
  $st = $conn->prepare("DELETE FROM faculty_classes WHERE id=?"); $st->bind_param('i', $id); $st->execute();
  header('Location: classes.php'); exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $fid = (int)$_POST['faculty_id']; $cid = (int)$_POST['course_id']; $sec = trim($_POST['section']);
  if (!$fid || !$cid || $sec === '') $msg = 'Fill all fields.';
  else {
    $st = $conn->prepare("INSERT IGNORE INTO faculty_classes (faculty_id,course_id,section) VALUES (?,?,?)");
    $st->bind_param('iis', $fid, $cid, $sec); $st->execute();
    $msg = 'Class assigned.';
  }
}
$faculty = $conn->query("SELECT id,name FROM users WHERE role='faculty' ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$courses = $conn->query("SELECT * FROM courses")->fetch_all(MYSQLI_ASSOC);
$rows = $conn->query("SELECT fc.id, u.name, c.code, fc.section FROM faculty_classes fc
  JOIN users u ON u.id=fc.faculty_id JOIN courses c ON c.id=fc.course_id ORDER BY u.name");
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Faculty Classes</title><link rel="stylesheet" href="../assets/style.css"></head>
<body class="layout">
<?php include '../includes/admin_nav.php'; ?>
<main>
  <h1>Faculty Classes</h1>
  <?php if ($msg): ?><div class="alert"><?= e($msg) ?></div><?php endif; ?>
  <form class="panel" method="post">
    <h2>Assign class to faculty</h2>
    <div class="grid">
      <div><label>Faculty</label><select name="faculty_id">
        <?php foreach ($faculty as $f): ?><option value="<?= $f['id'] ?>"><?= e($f['name']) ?></option><?php endforeach; ?></select></div>
      <div><label>Course</label><select name="course_id">
        <?php foreach ($courses as $c): ?><option value="<?= $c['id'] ?>"><?= e($c['code']) ?></option><?php endforeach; ?></select></div>
      <div><label>Section</label><input name="section" required></div>
    </div>
    <button class="btn" type="submit">Assign</button>
  </form>
  <div class="panel"><table>
    <tr><th>Faculty</th><th>Course</th><th>Section</th><th></th></tr>
    <?php while ($r = $rows->fetch_assoc()): ?>
    <tr><td><?= e($r['name']) ?></td><td><?= e($r['code']) ?></td><td><?= e($r['section']) ?></td>
      <td><a href="?delete=<?= $r['id'] ?>" onclick="return confirm('Remove?')">Remove</a></td></tr>
    <?php endwhile; ?>
  </table></div>
</main></body></html>
