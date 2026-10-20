CREATE DATABASE IF NOT EXISTS medical_appointment;
USE medical_appointment;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS appointments;
DROP TABLE IF EXISTS schedules;
DROP TABLE IF EXISTS doctors;
DROP TABLE IF EXISTS departments;
DROP TABLE IF EXISTS admins;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  contact VARCHAR(20) NOT NULL,
  address VARCHAR(200) NOT NULL,
  date_of_birth DATE NOT NULL,
  gender ENUM('Male','Female','Other') NOT NULL,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE admins (
  admin_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL
);

CREATE TABLE departments (
  department_id INT AUTO_INCREMENT PRIMARY KEY,
  department_name VARCHAR(100) NOT NULL UNIQUE,
  description VARCHAR(255)
);

CREATE TABLE doctors (
  doctor_id INT AUTO_INCREMENT PRIMARY KEY,
  department_id INT NOT NULL,
  doctor_name VARCHAR(100) NOT NULL,
  specialization VARCHAR(100) NOT NULL,
  available_days VARCHAR(40) NOT NULL,
  status ENUM('Available','Inactive') NOT NULL DEFAULT 'Available',
  FOREIGN KEY (department_id) REFERENCES departments(department_id)
);

CREATE TABLE schedules (
  schedule_id INT AUTO_INCREMENT PRIMARY KEY,
  doctor_id INT NOT NULL,
  appointment_date DATE NOT NULL,
  start_time TIME NOT NULL DEFAULT '08:00:00',
  end_time TIME NOT NULL DEFAULT '17:00:00',
  max_slots INT NOT NULL DEFAULT 100,
  UNIQUE (doctor_id, appointment_date),
  CHECK (max_slots BETWEEN 1 AND 100),
  FOREIGN KEY (doctor_id) REFERENCES doctors(doctor_id) ON DELETE CASCADE
);

CREATE TABLE appointments (
  appointment_id INT AUTO_INCREMENT PRIMARY KEY,
  appointment_no VARCHAR(30) NOT NULL UNIQUE,
  user_id INT NOT NULL,
  doctor_id INT NOT NULL,
  schedule_id INT NOT NULL,
  appointment_date DATE NOT NULL,
  appointment_time TIME NOT NULL,
  slot_number INT NULL,
  reason VARCHAR(255) NULL,
  status ENUM('Pending','Confirmed','Completed','Cancelled','No Show') NOT NULL DEFAULT 'Pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE (schedule_id, slot_number),
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  FOREIGN KEY (doctor_id) REFERENCES doctors(doctor_id) ON DELETE CASCADE,
  FOREIGN KEY (schedule_id) REFERENCES schedules(schedule_id) ON DELETE CASCADE
);

CREATE TABLE notifications (
  notification_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  appointment_id INT NULL,
  message VARCHAR(255) NOT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  FOREIGN KEY (appointment_id) REFERENCES appointments(appointment_id) ON DELETE SET NULL
);

INSERT INTO admins (name, username, password) VALUES
('Administrator', 'admin', '$2y$10$FEdCske2z7TD3OQvBPKxWugwilVLwrCvcrt7dT5CidhNtxnKKGaDu');

INSERT INTO departments (department_name, description) VALUES
('Pediatrics (Pedia)', 'Health care for babies, children and teenagers'),
('Obstetrics and Gynecology (OB-GYN)', 'Health care for women and pregnancy'),
('Cardiology', 'Heart and blood vessel problems'),
('Dermatology', 'Skin, hair and nail problems'),
('Internal Medicine', 'General health care for adults');

INSERT INTO doctors (department_id, doctor_name, specialization, available_days) VALUES
(1, 'Dr. Cyrus Hidalgo', 'Pediatrics', 'Mon,Tue,Wed,Thu,Fri'),
(2, 'Dr. Elaine Lim', 'OB-GYN', 'Mon,Tue,Wed,Thu'),
(3, 'Dr. Riguel Alleje', 'Cardiology', 'Mon,Wed,Fri'),
(4, 'Dr. Patricia Gomez', 'Dermatology', 'Mon,Tue,Thu,Fri'),
(5, 'Dr. Freya Natasha', 'Internal Medicine', 'Mon,Tue,Wed,Thu,Fri');

INSERT INTO schedules (doctor_id, appointment_date) VALUES
(1, '2026-10-12'), (1, '2026-10-13'), (1, '2026-10-14'), (1, '2026-10-15'), (1, '2026-10-16'), (1, '2026-10-19'), (1, '2026-10-20'), (1, '2026-10-21'), (1, '2026-10-22'), (1, '2026-10-23'),
(2, '2026-10-12'), (2, '2026-10-13'), (2, '2026-10-14'), (2, '2026-10-15'), (2, '2026-10-19'), (2, '2026-10-20'), (2, '2026-10-21'), (2, '2026-10-22'),
(3, '2026-10-12'), (3, '2026-10-14'), (3, '2026-10-16'), (3, '2026-10-19'), (3, '2026-10-21'), (3, '2026-10-23'),
(4, '2026-10-12'), (4, '2026-10-13'), (4, '2026-10-15'), (4, '2026-10-16'), (4, '2026-10-19'), (4, '2026-10-20'), (4, '2026-10-22'), (4, '2026-10-23'),
(5, '2026-10-12'), (5, '2026-10-13'), (5, '2026-10-14'), (5, '2026-10-15'), (5, '2026-10-16'), (5, '2026-10-19'), (5, '2026-10-20'), (5, '2026-10-21'), (5, '2026-10-22'), (5, '2026-10-23');
