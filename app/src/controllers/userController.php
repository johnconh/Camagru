<?php

require_once __DIR__.'/../models/user.php';

class UserController {

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

    private function validatePassword($password) {
        if (strlen($password) < 8) {
            return false;
        }
        if (!preg_match('/[A-Z]/', $password)) {
            return false;
        }
        if (!preg_match('/[a-z]/', $password)) {
            return false;
        }
        if (!preg_match('/[0-9]/', $password)) {
            return false;
        }
        return true;
    }


    public function editProfile() {
        session_start();

        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?page=login");
            exit();
        }

        $userID = $_SESSION['user_id'];
        $user = User::findByID($userID);

        if (!$user) {
            header("Location: index.php?page=login");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            $emailNotifications = isset($_POST['email_notifications']) ? 1 : 0;

            if(empty($username)) {
                $error = "Username cannot be empty.";
            } elseif (empty($email)) {
                $error = "A valid email is required.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Invalid email format.";
            } elseif (!empty($newPassword)) {
                if (empty($currentPassword)) {
                    $error = "Current password is required to set a new password.";
                } elseif (!User::verifyPassword($userID, $currentPassword)) {
                    $error = "Current password is incorrect.";
                } elseif ($newPassword !== $confirmPassword) {
                    $error = "New password and confirmation do not match.";
                } elseif (!$this->validatePassword($newPassword)) {
                    $error = "New password must be at least 8 characters long and include uppercase, lowercase letters, and numbers.";
                }
            }

            if (!isset($error)) {
                try{
                    $updateData = [
                        'username' => $username,
                        'email' => $email,
                        'email_notifications' => $emailNotifications
                    ];
    
                    if (!empty($newPassword)) {
                        $updateData['password'] = $newPassword;
                    }
    
                    $result = User::updateProfile($userID, $updateData);
                    if ($result) {
                        $success = "Profile updated successfully.";
                        $user = User::findByID($userID);
                    } else {
                        $error = "Failed to update profile. Please try again.";
                    }
                } catch (Exception $e) {
                    $error = $e->getMessage();
                }
            }
            if ($this->isAjax()) {
                if (isset($success)) {
                    $this->jsonResponse(true, $success);
                } else {
                    $this->jsonResponse(false, $error);
                }
            }
        }
        $view = '../src/views/user/editProfile.php';
        require_once '../src/views/layouts/main.php';
    }
}
