CREATE DATABASE IF NOT EXISTS remindme CHARACTER SET utf8mb4;
USE remindme;

CREATE TABLE courses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(20) NOT NULL UNIQUE,
  name VARCHAR(100) NOT NULL
);

CREATE TABLE categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','faculty','student') NOT NULL,
  course_id INT NULL,
  section VARCHAR(20) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL
);

CREATE TABLE posts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(150) NOT NULL,
  body TEXT NOT NULL,
  category_id INT NULL,
  course_id INT NULL,            -- NULL = visible to all courses
  section VARCHAR(20) NULL,      -- NULL = all sections
  author_id INT NOT NULL,
  status ENUM('active','archived') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  expires_at DATETIME NULL,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL,
  FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE post_reads (
  user_id INT NOT NULL,
  post_id INT NOT NULL,
  PRIMARY KEY (user_id, post_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
);

USE remindme;

INSERT INTO courses (code, name) VALUES
('BSIT', 'BS Information Technology'),
('BSBA', 'BS Business Administration'),
('BSHM', 'BS Hospitality Management'),
('BSCrim', 'BS Criminology'),
('BSED', 'Bachelor of Secondary Education'),
('BEED', 'Bachelor of Elementary Education'),
('CCJE', 'Criminal Justice Education'),
('MIDWIFERY', 'Midwifery'),
('BSA', 'BS Accountancy')
ON DUPLICATE KEY UPDATE name = VALUES(name);

ALTER TABLE categories ADD COLUMN for_faculty TINYINT(1) NOT NULL DEFAULT 0;
INSERT INTO categories (name, for_faculty) VALUES
('Class Schedule',1),('Quiz',1),('Class Announcement',1),('Exam',0);

-- Which class(es) (course + section) each faculty handles
CREATE TABLE faculty_classes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  faculty_id INT NOT NULL,
  course_id INT NOT NULL,
  section VARCHAR(20) NOT NULL,
  UNIQUE KEY uq_class (faculty_id, course_id, section),
  FOREIGN KEY (faculty_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);
