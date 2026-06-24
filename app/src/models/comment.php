<?php
// app/src/models/Comment.php

require_once __DIR__.'/../config/database.php';

class Comment {
    
    public static function create($userId, $photoId, $content) {
        $db = Database::getConnection();

        try {
            $stmt = $db->prepare("
                INSERT INTO comments (user_id, photo_id, content) 
                VALUES (:user_id, :photo_id, :content)
            ");

            $success = $stmt->execute([
                ':user_id' => $userId,
                ':photo_id' => $photoId,
                ':content' => htmlspecialchars($content, ENT_QUOTES, 'UTF-8')
            ]);

            if ($success) {
                return $db->lastInsertId();
            }

            return false;

        } catch (PDOException $e) {

            if ($e->errorInfo[1] == 1062) {
                return 'duplicate';
            }
            throw $e;
        }
    }
        
    public static function getPhotoComments($photoId) {
        $db = Database::getConnection();
        
        $stmt = $db->prepare("
            SELECT c.*, u.username 
            FROM comments c 
            JOIN users u ON c.user_id = u.id 
            WHERE c.photo_id = :photo_id 
            ORDER BY c.created_at DESC
        ");
        
        $stmt->execute([':photo_id' => $photoId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public static function delete($commentId, $userId) {
        $db = Database::getConnection();
        
        $stmt = $db->prepare("SELECT user_id FROM comments WHERE id = :id");
        $stmt->execute([':id' => $commentId]);
        $comment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$comment || $comment['user_id'] != $userId) {
            return false;
        }
        
        $stmt = $db->prepare("DELETE FROM comments WHERE id = :id");
        return $stmt->execute([':id' => $commentId]);
    }

    public static function existsByUserAndPhoto($userId, $photoId) {
        $stmt = Database::getConnection()->prepare("
            SELECT 1
            FROM comments
            WHERE user_id = :user_id AND photo_id = :photo_id
            LIMIT 1
        ");

        $stmt->execute([
            ':user_id' => $userId,
            ':photo_id' => $photoId
        ]);

        return (bool) $stmt->fetch();
    }
}