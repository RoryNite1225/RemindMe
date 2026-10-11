<?php
// Run ONCE in the browser: http://localhost/remindme/setup.php  then DELETE this file.
require 'config.php';

$courseCode = 'BSIT';
$courseName = 'BS Information Technology';
$courseCheck = $conn->query("SELECT id FROM courses WHERE code = 'BSIT' LIMIT 1");
if ($courseCheck && $courseCheck->num_rows === 0) {
  $courseStmt = $conn->prepare("INSERT INTO courses (code, name) VALUES (?, ?)");
  $courseStmt->bind_param('ss', $courseCode, $courseName);
  $courseStmt->execute();
}

$studentCourse = $conn->query("SELECT id FROM courses WHERE code = 'BSIT' LIMIT 1")->fetch_assoc();
$studentCourseId = $studentCourse['id'] ?? null;

$users = [
  ['Admin User',   'admin@remindme.test',   'admin123',   'admin'],
  ['Faculty User', 'faculty@remindme.test', 'faculty123', 'faculty'],
  ['Student User', 'student@remindme.test', 'student123', 'student'],
];
$st = $conn->prepare("INSERT IGNORE INTO users (name,email,password,role,course_id,section) VALUES (?,?,?,?,?,?)");
foreach ($users as [$n, $em, $pw, $r]) {
  $hash = password_hash($pw, PASSWORD_DEFAULT);
  $cid = $r === 'student' ? $studentCourseId : null;
  $sec = $r === 'student' ? 'A' : null;
  $st->bind_param('ssssis', $n, $em, $hash, $r, $cid, $sec);
  $st->execute();
}

$conn->query("INSERT IGNORE INTO faculty_classes (faculty_id, course_id, section)
  SELECT u.id, c.id, 'A' FROM users u, courses c
  WHERE u.email='faculty@remindme.test' AND c.code='BSIT'");

echo "Done. Accounts created. Delete setup.php now.";
