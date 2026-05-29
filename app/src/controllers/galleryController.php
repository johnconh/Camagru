<?php
// app/src/controllers/GalleryController.php

require_once __DIR__.'/../models/photo.php';
require_once __DIR__.'/../models/comment.php';
require_once __DIR__.'/../models/like.php';
require_once __DIR__.'/../models/user.php';
require_once __DIR__.'/../services/emailService.php';

class GalleryController {
    
    private function isAjax() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }
    
    private function jsonResponse($success, $message, $data = []) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ]);
        exit;
    }
    
    public function gallery() {
        session_start();
        
        $page = $_GET['page'] ?? 1;
        $page = max(1, intval($page));
        $perPage = 5;
        
        $photos = Photo::getAllPhotos($page, $perPage);
        $totalPhotos = Photo::countAllPhotos();
        $totalPages = ceil($totalPhotos / $perPage);
        
        $userId = $_SESSION['user_id'] ?? null;
        
        // Obtener comentarios y likes para cada foto
        foreach ($photos as $key => $photo) {
            $photos[$key]['comments'] = Comment::getPhotoComments($photo['id']);
            $photos[$key]['liked_by_me'] = $userId ? Like::exists($userId, $photo['id']) : false;
        }
                
        $view = '../src/views/gallery/gallery.php';
        require_once '../src/views/layouts/main.php';
    }
    
    public function addComment() {
        session_start();
        
        if (!isset($_SESSION['user_id'])) {
            $this->jsonResponse(false, 'You must be logged in');
        }
        
        $userId = $_SESSION['user_id'];
        $photoId = $_POST['photo_id'] ?? '';
        $content = $_POST['content'] ?? '';
        
        if (empty($photoId) || empty($content)) {
            $this->jsonResponse(false, 'Photo and comment content required');
        }
        
        // Crear comentario
        $commentId = Comment::create($userId, $photoId, $content);
        
        if ($commentId) {
            // Obtener datos de la foto y su autor
            $photo = Photo::findById($photoId);
            $author = User::findByID($photo['user_id']);
            $commenter = User::findByID($userId);
            
            // Enviar email al autor si tiene notificaciones habilitadas
            if ($author['email_notifications']) {
                EmailService::sendCommentNotification(
                    $author['email'],
                    $author['username'],
                    $commenter['username'],
                    $content
                );
            }
            
            // Obtener el comentario con datos del usuario
            $stmt = Database::getConnection()->prepare("
                SELECT c.*, u.username FROM comments c 
                JOIN users u ON c.user_id = u.id 
                WHERE c.id = :id
            ");
            $stmt->execute([':id' => $commentId]);
            $newComment = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $this->jsonResponse(true, 'Comment added', ['comment' => $newComment]);
        } else {
            $this->jsonResponse(false, 'Error adding comment');
        }
    }
    
    public function toggleLike() {
        session_start();
        
        if (!isset($_SESSION['user_id'])) {
            $this->jsonResponse(false, 'You must be logged in');
        }
        
        $userId = $_SESSION['user_id'];
        $photoId = $_POST['photo_id'] ?? '';
        
        if (empty($photoId)) {
            $this->jsonResponse(false, 'Photo required');
        }
        
        $liked = Like::exists($userId, $photoId);
        
        if ($liked) {
            Like::remove($userId, $photoId);
            $action = 'removed';
        } else {
            Like::add($userId, $photoId);
            $action = 'added';
        }
        
        // Obtener conteo actual de likes
        $stmt = Database::getConnection()->prepare("SELECT likes_count FROM photos WHERE id = :id");
        $stmt->execute([':id' => $photoId]);
        $photo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $this->jsonResponse(true, 'Like ' . $action, [
            'likes_count' => $photo['likes_count'],
            'action' => $action
        ]);
    }
}