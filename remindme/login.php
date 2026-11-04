<?php
// Handles the login form submitted from index.html
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: index.html'); exit; }

$email = trim($_POST['email'] ?? '');
$pass  = $_POST['password'] ?? '';

$st = $conn->prepare("SELECT * FROM users WHERE email=?");
$st->bind_param('s', $email);
$st->execute();
$u = $st->get_result()->fetch_assoc();

if ($u && password_verify($pass, $u['password'])) {
  if (in_array($u['role'], ['admin','faculty','student'], true)) {
    session_regenerate_id(true);
    $_SESSION['user'] = ['id'=>$u['id'],'name'=>$u['name'],'role'=>$u['role'],'course_id'=>$u['course_id'],'section'=>$u['section']];
    header('Location: ' . $u['role'] . '/dashboard.php'); exit;
  }
}

// wrong login: go back to the page and show the error
header('Location: index.html?error=1'); exit;
