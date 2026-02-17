CREATE TABLE schools (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  address VARCHAR(255),
  phone VARCHAR(50),
  status ENUM('active','inactive') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  school_id INT NOT NULL,
  role ENUM('admin','teacher','student') NOT NULL,
  username VARCHAR(80) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  language VARCHAR(5) DEFAULT 'en',
  status ENUM('active','inactive') DEFAULT 'active',
  last_login DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (school_id) REFERENCES schools(id)
);

CREATE TABLE teachers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  school_id INT NOT NULL,
  user_id INT NOT NULL,
  name VARCHAR(120) NOT NULL,
  whatsapp VARCHAR(40),
  nic VARCHAR(30),
  degree_details TEXT,
  subjects_text TEXT,
  age_group ENUM('primary','secondary','al') DEFAULT 'secondary',
  joined_at DATE,
  transferred_at DATE NULL,
  active TINYINT(1) DEFAULT 1,
  FOREIGN KEY (school_id) REFERENCES schools(id),
  FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE classes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  school_id INT NOT NULL,
  name VARCHAR(20) NOT NULL,
  year INT,
  section ENUM('primary','secondary','al') DEFAULT 'secondary',
  FOREIGN KEY (school_id) REFERENCES schools(id)
);

CREATE TABLE students (
  id INT AUTO_INCREMENT PRIMARY KEY,
  school_id INT NOT NULL,
  user_id INT NOT NULL,
  name VARCHAR(120) NOT NULL,
  address VARCHAR(255),
  whatsapp VARCHAR(40),
  class_id INT,
  age_group ENUM('primary','secondary','al') DEFAULT 'secondary',
  admitted_at DATE,
  left_at DATE NULL,
  active TINYINT(1) DEFAULT 1,
  FOREIGN KEY (school_id) REFERENCES schools(id),
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (class_id) REFERENCES classes(id)
);

CREATE TABLE timetable_entries (
  id INT AUTO_INCREMENT PRIMARY KEY,
  school_id INT NOT NULL,
  timetable_type ENUM('class','teacher') NOT NULL,
  owner_id INT NOT NULL,
  day_of_week VARCHAR(3) NOT NULL,
  period_no INT NOT NULL,
  class_id INT,
  teacher_id INT,
  subject VARCHAR(120),
  room VARCHAR(80),
  FOREIGN KEY (school_id) REFERENCES schools(id)
);

CREATE TABLE period_times (
  id INT AUTO_INCREMENT PRIMARY KEY,
  school_id INT NOT NULL,
  day_type ENUM('normal','special') DEFAULT 'normal',
  period_no INT NOT NULL,
  start_time TIME NOT NULL,
  label ENUM('period','register','interval') NOT NULL,
  FOREIGN KEY (school_id) REFERENCES schools(id)
);

CREATE TABLE absences (
  id INT AUTO_INCREMENT PRIMARY KEY,
  school_id INT NOT NULL,
  teacher_id INT NOT NULL,
  date DATE NOT NULL,
  source ENUM('online','physical') NOT NULL,
  reason VARCHAR(255),
  created_by INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (school_id) REFERENCES schools(id)
);

CREATE TABLE relief_assignments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  school_id INT NOT NULL,
  absence_id INT NOT NULL,
  date DATE NOT NULL,
  period_no INT NOT NULL,
  class_id INT NOT NULL,
  relief_teacher_id INT NOT NULL,
  status ENUM('auto','manual') DEFAULT 'auto',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (school_id) REFERENCES schools(id)
);

CREATE TABLE updates (
  id INT AUTO_INCREMENT PRIMARY KEY,
  school_id INT NOT NULL,
  audience ENUM('all','teachers','students') DEFAULT 'all',
  title VARCHAR(160) NOT NULL,
  body TEXT NOT NULL,
  publish_at DATETIME,
  created_by INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (school_id) REFERENCES schools(id)
);

CREATE TABLE test_updates (
  id INT AUTO_INCREMENT PRIMARY KEY,
  school_id INT NOT NULL,
  teacher_id INT NOT NULL,
  class_id INT NOT NULL,
  title VARCHAR(160) NOT NULL,
  test_date DATE,
  details TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (school_id) REFERENCES schools(id)
);

CREATE TABLE assignments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  school_id INT NOT NULL,
  teacher_id INT NOT NULL,
  class_id INT NOT NULL,
  title VARCHAR(160) NOT NULL,
  body TEXT,
  due_date DATE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (school_id) REFERENCES schools(id)
);

CREATE TABLE marks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  school_id INT NOT NULL,
  class_id INT NOT NULL,
  student_id INT NOT NULL,
  subject VARCHAR(120) NOT NULL,
  test_name VARCHAR(120) NOT NULL,
  mark DECIMAL(5,2) NOT NULL,
  updated_by INT NOT NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_marks (school_id,class_id,student_id,subject,test_name),
  FOREIGN KEY (school_id) REFERENCES schools(id)
);

CREATE TABLE leave_requests (
  id INT AUTO_INCREMENT PRIMARY KEY,
  school_id INT NOT NULL,
  teacher_id INT NOT NULL,
  date_from DATE NOT NULL,
  date_to DATE NOT NULL,
  reason TEXT,
  status ENUM('pending','approved','rejected') DEFAULT 'pending',
  decision_by INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (school_id) REFERENCES schools(id)
);

INSERT INTO schools (id,name,address,phone,status) VALUES (1,'Demo Sri Lankan School','Colombo','0771234567','active');
INSERT INTO users (school_id,role,username,password_hash,language,status) VALUES (1,'admin','admin1','$2y$10$43OTRkQWnY4hu9OYlDApEu5RgrE.3Nj3SIsJm9zq8abA/E1m8fP5q','en','active');
INSERT INTO classes (school_id,name,year,section) VALUES (1,'7C',2026,'secondary'),(1,'6A',2026,'primary');
INSERT INTO period_times (school_id,day_type,period_no,start_time,label) VALUES
(1,'normal',1,'07:45:00','period'),(1,'normal',0,'08:25:00','register'),(1,'normal',2,'08:30:00','period'),
(1,'normal',3,'09:10:00','period'),(1,'normal',4,'09:50:00','period'),(1,'normal',0,'10:30:00','interval'),
(1,'normal',5,'10:50:00','period'),(1,'normal',6,'11:30:00','period'),(1,'normal',7,'12:10:00','period'),(1,'normal',8,'12:50:00','period');
