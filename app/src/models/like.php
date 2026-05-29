<?php
// app/src/models/Like.php

require_once __DIR__.'/../config/database.php';

class Like {
    
    public static function add($userId, $photoId) {
        $db = Database::getConnection();
        
        // Verificar que no exista ya
        $stmt = $db->prepare("SELECT id FROM likes WHERE user_id = :user_id AND photo_id = :photo_id");
        $stmt->execute([':user_id' => $userId, ':photo_id' => $photoId]);
        if ($stmt->fetch()) {
            return false; // Ya existe
        }
        
        $stmt = $db->prepare("INSERT INTO likes (user_id, photo_id) VALUES (:user_id, :photo_id)");
        return $stmt->execute([':user_id' => $userId, ':photo_id' => $photoId]);
    }
    
    public static function remove($userId, $photoId) {
        $db = Database::getConnection();
        
        $stmt = $db->prepare("DELETE FROM likes WHERE user_id = :user_id AND photo_id = :photo_id");
        return $stmt->execute([':user_id' => $userId, ':photo_id' => $photoId]);
    }
    
    public static function exists($userId, $photoId) {
        $db = Database::getConnection();
        
        $stmt = $db->prepare("SELECT id FROM likes WHERE user_id = :user_id AND photo_id = :photo_id");
        $stmt->execute([':user_id' => $userId, ':photo_id' => $photoId]);
        return $stmt->fetch() !== false;
    }
}