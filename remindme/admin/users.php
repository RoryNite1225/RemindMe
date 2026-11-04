<?php
require '../config.php';
require_role('admin');
$msg = '';
$courses = $conn->query("SELECT * FROM courses")->fetch_all(MYSQLI_ASSOC);

if (isset($_GET['delete'])) {
  $id = (int)$_GET['delete'];
  if ($id !== (int)$_SESSION['user']['id']) {
    $st = $conn->prepare("DELETE FROM users WHERE id=?"); $st->bind_param('i', $id); $st->execute();
  }
  header('Location: users.php'); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name'] ?? ''); $email = trim($_POST['email'] ?? '');
  $role = $_POST['role'] ?? 'student'; $pass = $_POST['password'] ?? '';
  $cid  = (int)($_POST['course_id'] ?? 0) ?: null; $sec = trim($_POST['section'] ?? '') ?: null;

  if (!$name || !$email || strlen($pass) < 6 || !in_array($role, ['admin','faculty','student'], true)) {
    $msg = 'Fill all fields (password min 6 chars).';
  } elseif ($role === 'student' && (!$cid || !$sec)) {
    $msg = 'Students must have both a course and section.';
  } else {
    if ($role !== 'student') {
      $cid = null;
      $sec = null;
    }

    try {
      $hash = password_hash($pass, PASSWORD_DEFAULT);
      $st = $conn->prepare("INSERT INTO users (name,email,password,role,course_id,section) VALUES (?,?,?,?,?,?)");
      $st->bind_param('ssssis', $name, $email, $hash, $role, $cid, $sec);
      $st->execute();
      $msg = 'User added.';
    } catch (mysqli_sql_exception $e) {
      $msg = 'Email already exists.';
    }
  }
}
$users = $conn->query("SELECT u.*, c.code FROM users u LEFT JOIN courses c ON c.id=u.course_id ORDER BY u.role, u.name");
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Users</title><link rel="stylesheet" href="../assets/style.css"></head>
<body class="layout">
<?php include '../includes/admin_nav.php'; ?>
<main>
  <h1>Users</h1>
  <?php if ($msg): ?><div class="alert"><?= e($msg) ?></div><?php endif; ?>
  <form class="panel" method="post">
    <h2>Add User</h2>
    <div class="grid">
      <div><label>Name</label><input name="name" required></div>
      <div><label>Email</label><input type="email" name="email" required></div>
      <div><label>Password</label><input type="password" name="password" required></div>
      <div><label>Role</label><select name="role"><option>student</option><option>faculty</option><option>admin</option></select></div>
      <div><label>Course (students)</label><select name="course_id"><option value="0">-</option>
        <?php foreach ($courses as $c): ?><option value="<?= $c['id'] ?>"><?= e($c['code']) ?></option><?php endforeach; ?></select></div>
      <div><label>Section (students)</label><input name="section"></div>
    </div>
    <button class="btn" type="submit">Add user</button>
  </form>
  <div class="panel"><table>
    <tr><th>Name</th><th>Email</th><th>Role</th><th>Course/Sec</th><th></th></tr>
    <?php while ($u = $users->fetch_assoc()): ?>
    <tr><td><?= e($u['name']) ?></td><td><?= e($u['email']) ?></td><td><?= e($u['role']) ?></td>
      <td><?= e($u['code'] ?? '-') ?> <?= e($u['section'] ?? '') ?></td>
      <td><?php if ($u['id'] != $_SESSION['user']['id']): ?><a href="?delete=<?= $u['id'] ?>" onclick="return confirm('Delete user?')">Delete</a><?php endif; ?></td></tr>
    <?php endwhile; ?>
  </table></div>
</main></body></html>
