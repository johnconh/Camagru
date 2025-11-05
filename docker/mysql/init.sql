CREATE DATABASE IF NOT EXISTS camagru CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

USE camagru;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    is_verified BOOLEAN DEFAULT FALSE,
    verification_token VARCHAR(255) DEFAULT NULL,
    reset_token VARCHAR(255) DEFAULT NULL,
    reset_token_expires DATETIME DEFAULT NULL,
    email_notifications BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_verification_token (verification_token),
    INDEX idx_reset_token (reset_token)
);

CREATE TABLE IF NOT EXISTS photos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    filename VARCHAR(255) NOT NULL,
    original_filename VARCHAR(255) DEFAULT NULL,
    overlay_used VARCHAR(255) DEFAULT NULL,
    title VARCHAR(255) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    likes_count INT DEFAULT 0,
    comments_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at),
    INDEX idx_likes_count (likes_count)
);

CREATE TABLE IF NOT EXISTS likes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    photo_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (photo_id) REFERENCES photos(id) ON DELETE CASCADE,
    UNIQUE KEY unique_like (user_id, photo_id),
    INDEX idx_photo_id (photo_id),
    INDEX idx_user_id (user_id)
);

CREATE TABLE IF NOT EXISTS comments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    photo_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (photo_id) REFERENCES photos(id) ON DELETE CASCADE,
    INDEX idx_photo_id (photo_id),
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
);

DELIMITER $$

CREATE TRIGGER increment_likes_count 
    AFTER INSERT ON likes 
    FOR EACH ROW 
BEGIN
    UPDATE photos 
    SET likes_count = likes_count + 1 
    WHERE id = NEW.photo_id;
END$$

CREATE TRIGGER decrement_likes_count 
    AFTER DELETE ON likes 
    FOR EACH ROW 
BEGIN
    UPDATE photos 
    SET likes_count = likes_count - 1 
    WHERE id = OLD.photo_id;
END$$

CREATE TRIGGER increment_comments_count 
    AFTER INSERT ON comments 
    FOR EACH ROW 
BEGIN
    UPDATE photos 
    SET comments_count = comments_count + 1 
    WHERE id = NEW.photo_id;
END$$

CREATE TRIGGER decrement_comments_count 
    AFTER DELETE ON comments 
    FOR EACH ROW 
BEGIN
    UPDATE photos 
    SET comments_count = comments_count - 1 
    WHERE id = OLD.photo_id;
END$$

DELIMITER ;