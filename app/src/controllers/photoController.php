<?php

require_once __DIR__ . '/../models/photo.php';
require_once __DIR__ . '/../models/user.php';

class PhotoController {

    private function isAjax(){
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

    public function editor(){
        session_start();

        if (!isset($_SESSION['user_id'])) {
            if ($this->isAjax()) {
                $this->jsonResponse(false, "Unauthorized access.");
            } else {
                header('Location: index.php?page=login');
                exit;
            }
        }

        $userID = $_SESSION['user_id'];

        $overlays = $this->getOverlayImages();

        $userPhotos = Photo::getUserPhotos($userID);

        $view = '../src/views/photo/editor.php';
        require_once '../src/views/layouts/main.php';
    }

    private function getOverlayImages() {
        $overlayDir = __DIR__ . '/../../public/assets/overlays/';
        $overlays = [];
        if (is_dir($overlayDir)) {
            $files = scandir($overlayDir);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..' && preg_match('/\.(png)$/i', $file)) {
                    $overlays[] = [
                        'filename' => $file,
                        'path' => 'assets/images/overlays/' . $file
                    ];
                }
            }
        }
        return $overlays;
    }

    public function create(){
        session_start();

        if (!isset($_SESSION['user_id'])) {
            $this->jsonResponse(false, 'You must be logged in');
        }

        $userID = $_SESSION['user_id'];

        $overlay = $_POST['overlay'] ?? '';
        if(empty($overlay)) {
            $this->jsonResponse(false, 'Please select a filter');
        }

        if(!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
            $this->jsonResponse(false, 'Please upload an image');
        }

        $uploadImage = $_FILES['photo'];

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($uploadImage['type'], $allowedTypes)) {
            $this->jsonResponse(false, 'Invalid image type. Allowed types: JPEG, PNG, GIF');
        }

        $maxFileSize = 5 * 1024 * 1024;
        if ($uploadImage['size'] > $maxFileSize) {
            $this->jsonResponse(false, 'Image size exceeds 5MB limit');
        }

        try {
            $finalImage = $this->processImage($uploadImage['tmp_name'], $overlay);

            if(!$finalImage) {
                $this->jsonResponse(false, 'Error processing image');
            }

            $photo = Photo::create($userId, $finalImage, $uploadedFile['name'], $overlay);
            if ($photo) {
                $this->jsonResponse(true, 'Photo created successfully', ['photo' => $photo]);
            } else {
                $this->jsonResponse(false, 'Error saving photo');
            }
        } catch (Exception $e) {
            $this->jsonResponse(false, 'Error processing image: ' . $e->getMessage());
        }
    }

    private function processImage($imagePath, $overlayName) {
        $overlayPath = __DIR__ . '/../../public/assets/images/overlays/' . $overlayName;
        
        if (!file_exists($overlayPath)) {
            throw new Exception('Overlay not found');
        }
        
        $baseImage = $this->createImageFromFile($imagePath);
        if (!$baseImage) {
            throw new Exception('Could not load base image');
        }
        
        $overlay = imagecreatefrompng($overlayPath);
        if (!$overlay) {
            imagedestroy($baseImage);
            throw new Exception('Could not load overlay');
        }
        
        $baseWidth = imagesx($baseImage);
        $baseHeight = imagesy($baseImage);
        $overlayWidth = imagesx($overlay);
        $overlayHeight = imagesy($overlay);
        
        $resizedOverlay = imagecreatetruecolor($baseWidth, $baseHeight);
        imagealphablending($resizedOverlay, false);
        imagesavealpha($resizedOverlay, true);
        
        imagecopyresampled(
            $resizedOverlay, $overlay,
            0, 0, 0, 0,
            $baseWidth, $baseHeight,
            $overlayWidth, $overlayHeight
        );
        
        imagecopy($baseImage, $resizedOverlay, 0, 0, 0, 0, $baseWidth, $baseHeight);
        
        $filename = 'photo_' . time() . '_' . uniqid() . '.png';
        $uploadDir = __DIR__ . '/../../public/assets/images/uploads/';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $finalPath = $uploadDir . $filename;
        imagepng($baseImage, $finalPath);
        
        imagedestroy($baseImage);
        imagedestroy($overlay);
        imagedestroy($resizedOverlay);
        
        return $filename;
    }

    private function createImageFromFile($path) {
        $imageInfo = getimagesize($path);
        
        if (!$imageInfo) {
            return false;
        }
        
        switch ($imageInfo['mime']) {
            case 'image/jpeg':
            case 'image/jpg':
                return imagecreatefromjpeg($path);
            case 'image/png':
                return imagecreatefrompng($path);
            case 'image/gif':
                return imagecreatefromgif($path);
            default:
                return false;
        }
    }

    public function delete(){
        session_start();

        if (!isset($_SESSION['user_id'])) {
            $this->jsonResponse(false, 'You must be logged in');
        }

        $userID = $_SESSION['user_id'];
        $photoID = $_POST['photo_id'] ?? '';

        if (empty($photoID)) {
            $this->jsonResponse(false, 'Photo ID is required');
        }

        $success = Photo::delete($photoID, $userID);
        if ($success) {
            $this->jsonResponse(true, 'Photo deleted successfully');
        } else {
            $this->jsonResponse(false, 'Error deleting photo');
        }
    }
}