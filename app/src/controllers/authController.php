<?php
require_once __DIR__.'/../models/user.php';
require_once __DIR__.'/../services/emailService.php';

class AuthController {
    
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
    
    public function register(){
        session_start();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm = $_POST['confirm'] ?? '';
        
            if (empty($username) || empty($email) || empty($password) || empty($confirm)) {
                $error = "All fields are required.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Invalid email format.";
            } elseif ($password !== $confirm) {
                $error = "Passwords do not match.";
            } elseif (!$this->validatePassword($password)) {
                $error = "Password must be at least 8 characters long and contain uppercase, lowercase, and numbers.";
            } else {
                $db = Database::getConnection();
                $stmt = $db->prepare("SELECT id FROM users WHERE username = :username OR email = :email");
                $stmt->execute(['username' => $username, 'email' => $email]);
                if ($stmt->fetch()) {
                    $error = "Username or email already exists.";
                } else {
                    $token = User::register($username, $email, $password);
                    if ($token) {
                        EmailService::sendVerificationEmail($email, $token);
                        $success = "Registration successful! Please check your email to verify your account.";
                    } else {
                        $error = "Registration failed. Please try again.";
                    }
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
        
        $view = '../src/views/auth/register.php';
        require_once '../src/views/layouts/main.php';
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

    public function login(){
        session_start();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            $user = User::login($username, $password);
            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                
                if ($this->isAjax()) {
                    $this->jsonResponse(true, "Login successful", ['redirect' => 'index.php?page=home']);
                }
                
                header("Location: index.php?page=home");
                exit();
            } else {
                $error = "Invalid username or password, or account not verified.";
                
                if ($this->isAjax()) {
                    $this->jsonResponse(false, $error);
                }
            }
        }
        
        $view = '../src/views/auth/login.php';
        require_once '../src/views/layouts/main.php';
    }

    public function logout(){
        session_start();
        session_destroy();
        header("Location: index.php?page=login");
        exit();
    }

    public function verify(){
        session_start();
        $token = $_GET['token'] ?? '';
        if($token) {
            $success = User::verifyToken($token);
            $message = $success ? "Account verified successfully! You can now log in." : "Invalid or expired token.";
        } else {
            $message = "No token provided.";
        }
        $view = '../src/views/auth/verify.php';
        require_once '../src/views/layouts/main.php';
    }

    public function forgotPassword(){
        session_start();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
        
            if (empty($email)) {
                $error = "Email is required.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Invalid email format.";
            } else {
                $token = User::createResetToken($email);
                if ($token) {
                    EmailService::sendPasswordResetEmail($email, $token);
                    $success = "Password reset email sent! Please check your inbox.";
                } else {
                    $error = "Error, please try again. Verify that the email is correct.";
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

        $view = '../src/views/auth/forgotPassword.php';
        require_once '../src/views/layouts/main.php';
    }

    public function resetPassword(){
        session_start();
        $token = $_GET['token'] ?? $_POST['token'] ?? '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'] ?? '';
            $confirm = $_POST['confirm'] ?? '';

            if(empty($token)) {
                $error = "No token provided.";
            } elseif (empty($password) || empty($confirm)) {
                $error = "All fields are required.";
            } elseif ($password !== $confirm) {
                $error = "Passwords do not match.";
            } elseif (!$this->validatePassword($password)) {
                $error = "Password must be at least 8 characters long and contain uppercase, lowercase, and numbers.";
            } else {
                $success = User::resetPassword($token, $password);
                if ($success) {
                    $message = "Password reset successful! You can now log in.";
                } else {
                    $error = "Invalid or expired token.";
                }
            }

            if ($this->isAjax()) {
                if (isset($message)) {
                    $this->jsonResponse(true, $message);
                } elseif (isset($success)) {
                    $this->jsonResponse(true, "Password reset successfully!");
                } else {
                    $this->jsonResponse(false, $error);
                }
            }
        }

        if (empty($token) && !User::verifyResetToken($token)) {
            $error = "Invalid or expired token.";
        }

        $view = '../src/views/auth/resetPassword.php';
        require_once '../src/views/layouts/main.php';
    }

}