<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: register.html');
  exit;
}

$name = trim($_POST['name'] ?? '');
$email = trim(strtolower($_POST['email'] ?? ''));
$password = $_POST['password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';
$courseCode = strtoupper(trim((string)($_POST['course_code'] ?? '')));
$courseId = (int)($_POST['course_id'] ?? 0);
$section = trim(strtoupper($_POST['section'] ?? ''));

if ($courseCode === '' && $courseId > 0) {
  $courseCode = $conn->query("SELECT code FROM courses WHERE id = $courseId LIMIT 1")->fetch_assoc()['code'] ?? '';
}

if (!$name || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6 || $password !== $confirm || $courseCode === '' || $section === '') {
  header('Location: register.html?error=invalid');
  exit;
}

$course = $conn->prepare('SELECT id FROM courses WHERE code = ? LIMIT 1');
$course->bind_param('s', $courseCode);
$course->execute();
$courseResult = $course->get_result();
$courseRow = $courseResult->fetch_assoc();
if (!$courseRow) {
  header('Location: register.html?error=invalid');
  exit;
}
$courseId = (int)$courseRow['id'];

try {
  $existing = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
  $existing->bind_param('s', $email);
  $existing->execute();
  $existingResult = $existing->get_result();

  if ($existingResult->num_rows > 0) {
    header('Location: register.html?error=duplicate');
    exit;
  }

  $hash = password_hash($password, PASSWORD_DEFAULT);
  $stmt = $conn->prepare('INSERT INTO users (name, email, password, role, course_id, section) VALUES (?, ?, ?, ?, ?, ?)');
  $role = 'student';
  $stmt->bind_param('ssssis', $name, $email, $hash, $role, $courseId, $section);
  $stmt->execute();

  header('Location: index.html?registered=1');
  exit;
} catch (mysqli_sql_exception $e) {
  header('Location: register.html?error=duplicate');
  exit;
}
