<?php

require_once __DIR__ . '/../models/photo.php';
require_once __DIR__ . '/../models/user.php';

class PhotoController {

    /* ========================================= */
    /* AJAX CHECK */
    /* ========================================= */

    private function isAjax(){

        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }

    /* ========================================= */
    /* JSON RESPONSE */
    /* ========================================= */

    private function jsonResponse($success, $message, $data = []) {

        header('Content-Type: application/json');

        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ]);

        exit;
    }

    /* ========================================= */
    /* EDITOR PAGE */
    /* ========================================= */

    public function editor(){

        session_start();

        if (!isset($_SESSION['user_id'])) {

            if ($this->isAjax()) {

                $this->jsonResponse(false, "Unauthorized access");

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

    /* ========================================= */
    /* GET STICKERS */
    /* ========================================= */

    private function getOverlayImages(){

        $overlayDir = __DIR__ . '/../../public/assets/images/overlays/';

        $overlays = [];

        if (is_dir($overlayDir)) {

            $files = scandir($overlayDir);

            foreach ($files as $file) {

                if (
                    $file !== '.' &&
                    $file !== '..' &&
                    preg_match('/\.(png|svg)$/i', $file)
                ) {

                    $overlays[] = [
                        'filename' => $file,
                        'path' => 'assets/images/overlays/' . $file
                    ];
                }
            }
        }

        return $overlays;
    }

    /* ========================================= */
    /* CREATE PHOTO */
    /* ========================================= */

    public function create(){

        session_start();

        if (!isset($_SESSION['user_id'])) {

            $this->jsonResponse(false, 'You must be logged in');
        }

        $userID = $_SESSION['user_id'];

        /* ========================================= */
        /* STICKERS */
        /* ========================================= */

        $stickers = json_decode(
            $_POST['stickers'] ?? '[]',
            true
        );

        if (empty($stickers)) {

            $this->jsonResponse(
                false,
                'Please add at least one sticker'
            );
        }

        /* ========================================= */
        /* IMAGE VALIDATION */
        /* ========================================= */

        if (
            !isset($_FILES['image']) ||
            $_FILES['image']['error'] !== UPLOAD_ERR_OK
        ) {

            $this->jsonResponse(false, 'Please upload an image');
        }

        $uploadImage = $_FILES['image'];

        $allowedTypes = [
            'image/jpeg',
            'image/png',
            'image/gif'
        ];

        if (!in_array($uploadImage['type'], $allowedTypes)) {

            $this->jsonResponse(
                false,
                'Invalid image type'
            );
        }

        $maxFileSize = 5 * 1024 * 1024;

        if ($uploadImage['size'] > $maxFileSize) {

            $this->jsonResponse(
                false,
                'Image size exceeds 5MB limit'
            );
        }

        /* ========================================= */
        /* PROCESS IMAGE */
        /* ========================================= */

        try {

            $finalImage = $this->processImage(
                $uploadImage['tmp_name'],
                $stickers
            );

            if (!$finalImage) {

                $this->jsonResponse(
                    false,
                    'Error processing image'
                );
            }

            $photo = Photo::create(
                $userID,
                $finalImage,
                $uploadImage['name']
            );

            if ($photo) {

                $this->jsonResponse(
                    true,
                    'Photo created successfully',
                    [
                        'photo' => $photo
                    ]
                );

            } else {

                $this->jsonResponse(
                    false,
                    'Error saving photo'
                );
            }

        } catch (Exception $e) {

            $this->jsonResponse(
                false,
                'Error processing image: ' . $e->getMessage()
            );
        }
    }

    /* ========================================= */
    /* PROCESS IMAGE WITH STICKERS */
    /* ========================================= */

    private function processImage($imagePath, $stickers){

        $baseImage = $this->createImageFromFile($imagePath);

        if (!$baseImage) {

            throw new Exception('Could not load base image');
        }

        $baseWidth = imagesx($baseImage);
        $baseHeight = imagesy($baseImage);

        foreach ($stickers as $stickerData) {

            if (empty($stickerData['filename'])) {
                continue;
            }

            $overlayPath = __DIR__ .
                '/../../public/assets/images/overlays/' .
                $stickerData['filename'];

            if (!file_exists($overlayPath)) {
                continue;
            }

            $sticker = imagecreatefrompng($overlayPath);

            if (!$sticker) {
                continue;
            }

            /* ========================================= */
            /* ORIGINAL STICKER SIZE */
            /* ========================================= */

            $originalWidth = imagesx($sticker);
            $originalHeight = imagesy($sticker);

            /* ========================================= */
            /* NEW SIZE */
            /* ========================================= */

            $newWidth = intval($stickerData['width'] ?? 120);

            $newHeight = intval(
                ($originalHeight / $originalWidth) * $newWidth
            );

            /* ========================================= */
            /* CREATE RESIZED STICKER */
            /* ========================================= */

            $resizedSticker = imagecreatetruecolor(
                $newWidth,
                $newHeight
            );

            imagealphablending($resizedSticker, false);

            imagesavealpha($resizedSticker, true);

            $transparent = imagecolorallocatealpha(
                $resizedSticker,
                0,
                0,
                0,
                127
            );

            imagefill(
                $resizedSticker,
                0,
                0,
                $transparent
            );

            imagecopyresampled(
                $resizedSticker,
                $sticker,
                0,
                0,
                0,
                0,
                $newWidth,
                $newHeight,
                $originalWidth,
                $originalHeight
            );

            /* ========================================= */
            /* POSITION */
            /* ========================================= */

            $x = intval($stickerData['x'] ?? 0);
            $y = intval($stickerData['y'] ?? 0);

            /* ========================================= */
            /* ADD STICKER TO IMAGE */
            /* ========================================= */

            imagecopy(
                $baseImage,
                $resizedSticker,
                $x,
                $y,
                0,
                0,
                $newWidth,
                $newHeight
            );

            /* ========================================= */
            /* CLEAN MEMORY */
            /* ========================================= */

            imagedestroy($sticker);

            imagedestroy($resizedSticker);
        }

        /* ========================================= */
        /* SAVE FINAL IMAGE */
        /* ========================================= */

        $filename = 'photo_' . time() . '_' . uniqid() . '.png';

        $uploadDir = __DIR__ .
            '/../../public/assets/images/uploads/';

        if (!is_dir($uploadDir)) {

            mkdir($uploadDir, 0755, true);
        }

        $finalPath = $uploadDir . $filename;

        imagepng($baseImage, $finalPath);

        imagedestroy($baseImage);

        return $filename;
    }

    /* ========================================= */
    /* CREATE IMAGE FROM FILE */
    /* ========================================= */

    private function createImageFromFile($path){

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

    /* ========================================= */
    /* DELETE PHOTO */
    /* ========================================= */

    public function delete(){

        session_start();

        if (!isset($_SESSION['user_id'])) {

            $this->jsonResponse(
                false,
                'You must be logged in'
            );
        }

        $userID = $_SESSION['user_id'];

        $photoID = $_POST['photo_id'] ?? '';

        if (empty($photoID)) {

            $this->jsonResponse(
                false,
                'Photo ID is required'
            );
        }

        $success = Photo::delete($photoID, $userID);

        if ($success) {

            $this->jsonResponse(
                true,
                'Photo deleted successfully'
            );

        } else {

            $this->jsonResponse(
                false,
                'Error deleting photo'
            );
        }
    }
}