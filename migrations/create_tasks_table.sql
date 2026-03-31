CREATE DATABASE IF NOT EXISTS task_management;
USE task_management;

CREATE TABLE IF NOT EXISTS tasks (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title      VARCHAR(255)                          NOT NULL,
    due_date   DATE                                  NOT NULL,
    priority   ENUM('low', 'medium', 'high')         NOT NULL,
    status     ENUM('pending', 'in_progress', 'done') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_title_due_date (title, due_date)
);
