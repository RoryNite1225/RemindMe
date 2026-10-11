<?php $cur = basename($_SERVER['PHP_SELF']); ?>
<nav class="sidebar">
  <h2>RemindMe</h2>
  <a href="dashboard.php" class="<?= $cur === 'dashboard.php' ? 'active' : '' ?>">Dashboard</a>
  <a href="posts.php"     class="<?= $cur === 'posts.php' ? 'active' : '' ?>">Manage Posts</a>
  <a href="users.php"     class="<?= $cur === 'users.php' ? 'active' : '' ?>">Users</a>
  <a href="classes.php"   class="<?= $cur === 'classes.php' ? 'active' : '' ?>">Faculty Classes</a>
  <a href="../logout.php" class="logout">Logout (<?= e($_SESSION['user']['name']) ?>)</a>
</nav>
