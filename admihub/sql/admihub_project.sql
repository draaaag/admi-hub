-- Create database
CREATE DATABASE IF NOT EXISTS admihub;
USE admihub;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    profile_pic VARCHAR(255),                -- URL or path to profile image
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    about_me TEXT,                           -- Optional profile bio
    role ENUM('student', 'moderator', 'admin') DEFAULT 'student',
    is_active TINYINT(1) DEFAULT 1,          -- Allows for deactivating accounts
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create courses table
CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT
);

CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    title VARCHAR(255),
    description TEXT,
    file_path VARCHAR(255),
    is_highlight TINYINT(1) DEFAULT 0,
    approved TINYINT(1) DEFAULT 0,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (course_id) REFERENCES courses(id)
);

CREATE TABLE project_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NULL, -- keep for reference but allow NULL for deleted projects
    project_title VARCHAR(255) NOT NULL,
    course_name VARCHAR(255) DEFAULT '-',
    user_id INT NOT NULL, -- submitted by
    submitter_name VARCHAR(255) NOT NULL,
    approved_rejected_by VARCHAR(255) DEFAULT NULL,
    action ENUM('submitted', 'approved', 'rejected', 'deleted') NOT NULL,
    deleted_by VARCHAR(255) DEFAULT NULL,
    submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    deleted_at DATETIME DEFAULT NULL,
    performed_by INT NOT NULL, -- ID of the user performing the action
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (performed_by) REFERENCES users(id)
);



-- Insert default courses
INSERT INTO courses (name, description) VALUES
('Music Production', 'Course in producing music tracks'),
('Sound Engineering', 'Course in mastering audio and studio sound'),
('Graphic Design', 'Course in visual and print design'),
('Video Game Design', 'Course in creating and developing games'),
('2D & 3D Animation', 'Course in animated content creation'),
('Film & TV Production', 'Course in media and film creation'),
('Video Production', 'Course in producing video content');

-- Password = admin123 (hashed)
INSERT INTO users (username, email, password, role, first_name, last_name)
VALUES (
  'testadmin',
  'admin@gmail.com',
  '$2y$10$YuuSc5AAB4x33QNGVD2wVuGzKlo6LyBihcd4u2riTycjdjA30xe2e',
  'admin',
  'Test',
  'Admin'
);


