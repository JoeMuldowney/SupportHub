-- Create Database
CREATE DATABASE IF NOT EXISTS ticket_system_db;

USE ticket_system_db;

-- =========================
-- USERS TABLE
-- =========================
DROP TABLE IF EXISTS users;

CREATE TABLE users (
  user_id INT NOT NULL AUTO_INCREMENT,
  first_name VARCHAR(50) NOT NULL,
  last_name VARCHAR(50) NOT NULL,
  email VARCHAR(100) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role INT DEFAULT NULL,
  manager VARCHAR(225) DEFAULT NULL,
  dept VARCHAR(25) DEFAULT NULL,
  PRIMARY KEY (user_id),
  UNIQUE KEY (email)
) ENGINE=InnoDB;

-- =========================
-- TASK TABLE
-- =========================
DROP TABLE IF EXISTS task;

CREATE TABLE task (
  id INT NOT NULL AUTO_INCREMENT,
  user_id INT NOT NULL,
  location VARCHAR(255) NOT NULL,
  priority VARCHAR(50) NOT NULL,
  status VARCHAR(50) NOT NULL,
  user_desc TEXT NOT NULL,
  date_opened DATE NOT NULL,
  date_updated DATE DEFAULT NULL,
  date_closed DATE DEFAULT NULL,
  solution TEXT,
  opened_by VARCHAR(50) NOT NULL,
  updated_by VARCHAR(50) DEFAULT NULL,
  closed_by VARCHAR(50) DEFAULT NULL,
  category VARCHAR(50) NOT NULL,
  manager VARCHAR(225) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY (user_id),
  CONSTRAINT fk_task_user
    FOREIGN KEY (user_id)
    REFERENCES users(user_id)
    ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================
-- IMAGE TABLE
-- =========================
DROP TABLE IF EXISTS image;

CREATE TABLE image (
  id INT NOT NULL AUTO_INCREMENT,
  ticket_id INT DEFAULT NULL,
  name VARCHAR(225) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY (ticket_id),
  CONSTRAINT fk_image_task
    FOREIGN KEY (ticket_id)
    REFERENCES task(id)
    ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================
-- EMAIL TABLE
-- =========================
DROP TABLE IF EXISTS email;

CREATE TABLE email (
  id INT NOT NULL AUTO_INCREMENT,
  user_email VARCHAR(225) DEFAULT NULL,
  supervisor_email VARCHAR(225) DEFAULT NULL,
  location VARCHAR(50) DEFAULT NULL,
  status VARCHAR(50) DEFAULT NULL,
  priority VARCHAR(50) DEFAULT NULL,
  user_desc TEXT DEFAULT NULL,
  category VARCHAR(50) DEFAULT NULL,
  solution TEXT DEFAULT NULL,
  email_counter INT DEFAULT NULL,
  ticket_num INT DEFAULT NULL,
  user_id INT DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

-- =========================
-- SCC USER TABLE
-- =========================
DROP TABLE IF EXISTS scc_user;

CREATE TABLE scc_user (
  id INT NOT NULL AUTO_INCREMENT,
  fname VARCHAR(225) NOT NULL,
  lname VARCHAR(225) NOT NULL,
  email VARCHAR(225) NOT NULL,
  pname VARCHAR(225) NOT NULL,
  supervisor VARCHAR(225) NOT NULL,
  location VARCHAR(225) NOT NULL,
  dept VARCHAR(225) NOT NULL,
  title VARCHAR(225) NOT NULL,
  position VARCHAR(225) NOT NULL,
  hours DECIMAL(4,2) DEFAULT NULL,
  sdate DATE NOT NULL,
  avaya VARCHAR(5) DEFAULT NULL,
  shadow_agent VARCHAR(5) DEFAULT NULL,
  ecirts VARCHAR(5) DEFAULT NULL,
  dots VARCHAR(5) DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

-- =========================
-- SCC USER Groups & Folders TABLE
-- =========================
DROP TABLE IF EXISTS groups_folders;

CREATE TABLE groups_folders (
  id INT NOT NULL AUTO_INCREMENT,
  user_id INT NOT NULL,
  email VARCHAR(225) DEFAULT NULL,
  xdrive VARCHAR(225) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY (user_id),
  CONSTRAINT fk_groups_folders_user
    FOREIGN KEY (user_id)
    REFERENCES scc_user(id)
    ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================
-- Ticket Comment History TABLE
-- =========================
DROP TABLE IF EXISTS comment_history;

CREATE TABLE comment_history (
  id INT NOT NULL AUTO_INCREMENT,
  ticket_id INT NOT NULL, 
  email VARCHAR(225) DEFAULT NULL,
  comment VARCHAR(225) DEFAULT NULL,
  timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY (ticket_id),
  CONSTRAINT fk_comment_history_ticket
    FOREIGN KEY (ticket_id)
    REFERENCES task(id)
    ON DELETE CASCADE
) ENGINE=InnoDB;


INSERT INTO users (first_name, last_name, email, password_hash, role, manager, dept) VALUES
('Joe', 'Muldowney', 'example@example.com', '$2y$10$y6LVlGcgE14CuNELFIxoDumzRGVGQlGKppjEGlCAfaOYXS7LVfPvq', 3, '', 'MIS'); -- Temp User'