CREATE DATABASE file_repo;
USE file_repo;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL
);

CREATE TABLE folders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  folder_name VARCHAR(255) NOT NULL,
  parent_folder_id INT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (parent_folder_id) REFERENCES folders(id),
  UNIQUE (user_id, folder_name, parent_folder_id)
);

CREATE TABLE files (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  folder_id INT DEFAULT NULL,
  filename VARCHAR(255) NOT NULL,
  path VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (folder_id) REFERENCES folders(id),
  UNIQUE (user_id, folder_id, filename)
);
