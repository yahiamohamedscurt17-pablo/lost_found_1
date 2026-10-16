CREATE DATABASE IF NOT EXISTS lost111 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE lost111;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  class_code ENUM('A1','A2','A3','A4','A5','A6','B1','B2','B3','B4','B5','B6','C1','C2','C3','C4','C5','C6') NULL,
  role ENUM('student','admin') NOT NULL DEFAULT 'student',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS reports (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  report_type ENUM('lost','found') NOT NULL,
  item_name VARCHAR(140) NOT NULL,
  category VARCHAR(80) NOT NULL,
  description TEXT NOT NULL,
  location VARCHAR(180) NOT NULL,
  event_date DATE NOT NULL,
  image_path VARCHAR(255) NULL,
  related_report_id INT UNSIGNED NULL,
  status ENUM('open','matched','returned','closed') NOT NULL DEFAULT 'open',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_reports_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_reports_related FOREIGN KEY (related_report_id) REFERENCES reports(id) ON DELETE SET NULL,
  INDEX idx_reports_type_status (report_type, status),
  INDEX idx_reports_class_user (user_id),
  INDEX idx_reports_related (related_report_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS messages (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  report_id INT UNSIGNED NOT NULL,
  sender_id INT UNSIGNED NOT NULL,
  message_text VARCHAR(1500) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_messages_report FOREIGN KEY (report_id) REFERENCES reports(id) ON DELETE CASCADE,
  CONSTRAINT fk_messages_sender FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_messages_report (report_id, created_at)
) ENGINE=InnoDB;

-- بعد إنشاء حسابك من الموقع، يمكنك جعله مديرًا بتنفيذ:
-- UPDATE users SET role='admin' WHERE email='your-email@example.com';
