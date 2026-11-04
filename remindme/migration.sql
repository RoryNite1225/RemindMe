USE remindme;
ALTER TABLE categories ADD COLUMN for_faculty TINYINT(1) NOT NULL DEFAULT 0;
INSERT INTO categories (name, for_faculty) VALUES
('Class Schedule',1),('Quiz',1),('Class Announcement',1),('Exam',0);

CREATE TABLE faculty_classes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  faculty_id INT NOT NULL,
  course_id INT NOT NULL,
  section VARCHAR(20) NOT NULL,
  UNIQUE KEY uq_class (faculty_id, course_id, section),
  FOREIGN KEY (faculty_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- Give the test faculty (faculty@remindme.test) the class BSIT - A
INSERT IGNORE INTO faculty_classes (faculty_id, course_id, section)
SELECT u.id, c.id, 'A' FROM users u, courses c
WHERE u.email='faculty@remindme.test' AND c.code='BSIT';
