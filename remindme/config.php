<?php
date_default_timezone_set('Asia/Manila');
session_start();

$db_host = getenv('DB_HOST') ?: 'localhost';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASS') ?: '';
$db_name = getenv('DB_NAME') ?: 'remindme';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) die('DB connection failed: ' . $conn->connect_error);
$conn->set_charset('utf8mb4');
$conn->query("SET time_zone = '+08:00'");

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function require_role($role) {
  if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== $role) {
    header('Location: ../index.html'); exit;
  }
}

// Expired posts automatically get archived
function auto_archive($conn) {
  $conn->query("UPDATE posts SET status='archived' WHERE status='active' AND expires_at IS NOT NULL AND expires_at < NOW()");
}
