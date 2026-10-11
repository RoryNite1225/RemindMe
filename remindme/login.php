<?php
// Handles the login form submitted from index.html
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: index.html'); exit; }

$email = trim($_POST['email'] ?? '');
$pass  = $_POST['password'] ?? '';
$role  = $_POST['role'] ?? 'student';
if (!in_array($role, ['admin','faculty','student'], true)) $role = 'student';

$st = $conn->prepare("SELECT * FROM users WHERE email=? AND role=?");
$st->bind_param('ss', $email, $role);
$st->execute();
$u = $st->get_result()->fetch_assoc();

if ($u && password_verify($pass, $u['password'])) {
  session_regenerate_id(true);
  $_SESSION['user'] = ['id'=>$u['id'],'name'=>$u['name'],'role'=>$u['role'],'course_id'=>$u['course_id'],'section'=>$u['section']];
  header('Location: ' . $u['role'] . '/dashboard.php'); exit;
}

// wrong login: go back to the page and show the error
header('Location: index.html?error=1&role=' . urlencode($role)); exit;
