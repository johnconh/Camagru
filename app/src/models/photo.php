<?php

require_once __DIR__.'/../config/database.php';

class Photo {
    public static function create($userId, $filename, $originalFilename, $overlay) {
        $db = Database::getConnection();
        
        $stmt = $db->prepare("
            INSERT INTO photos (user_id, filename, original_filename, overlay_used) 
            VALUES (:user_id, :filename, :original_filename, :overlay)
        ");
        
        $success = $stmt->execute([
            ':user_id' => $userId,
            ':filename' => $filename,
            ':original_filename' => $originalFilename,
            ':overlay' => $overlay
        ]);
        
        if ($success) {
            return [
                'id' => $db->lastInsertId(),
                'filename' => $filename,
                'path' => 'assets/images/uploads/' . $filename
            ];
        }
        
        return false;
    }

    public static function getUserPhotos($userId, $limit = 20) {
        $db = Database::getConnection();
        
        $stmt = $db->prepare("
            SELECT id, filename, overlay_used, likes_count, comments_count, created_at 
            FROM photos 
            WHERE user_id = :user_id 
            ORDER BY created_at DESC 
            LIMIT :limit
        ");
        
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public static function getAllPhotos($page = 1, $perPage = 5) {
        $db = Database::getConnection();
        
        $offset = ($page - 1) * $perPage;
        
        $stmt = $db->prepare("
            SELECT p.*, u.username 
            FROM photos p 
            JOIN users u ON p.user_id = u.id 
            ORDER BY p.created_at DESC 
            LIMIT :limit OFFSET :offset
        ");
        
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public static function countAllPhotos() {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT COUNT(*) as total FROM photos");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
    
    public static function delete($photoId, $userId) {
        $db = Database::getConnection();
        
        $stmt = $db->prepare("SELECT filename FROM photos WHERE id = :id AND user_id = :user_id");
        $stmt->execute([':id' => $photoId, ':user_id' => $userId]);
        $photo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$photo) {
            return false;
        }
        
        $filePath = __DIR__ . '/../../public/assets/images/uploads/' . $photo['filename'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        
        $stmt = $db->prepare("DELETE FROM photos WHERE id = :id AND user_id = :user_id");
        return $stmt->execute([':id' => $photoId, ':user_id' => $userId]);
    }
    
    public static function findById($photoId) {
        $db = Database::getConnection();
        
        $stmt = $db->prepare("
            SELECT p.*, u.username 
            FROM photos p 
            JOIN users u ON p.user_id = u.id 
            WHERE p.id = :id
        ");
        
        $stmt->execute([':id' => $photoId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}