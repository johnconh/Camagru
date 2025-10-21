<?php
require_once __DIR__.'/../models/user.php';
require_once __DIR__.'/../services/emailService.php';

class AuthController {
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
                $db = Database::getConection();
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
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            
            $user = User::login($email, $password);
            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                header("Location: index.php?page=home");
                exit();
            } else {
                $error = "Invalid email or password, or account not verified.";
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
        if($token)
        {
            $success = User::verifyToken($token);
            $message = $success ? "Account verified successfully! You can now log in." : "Invalid or expired token.";
        } else {
            $message = "No token provided.";
        }
        $view = '../src/views/auth/verify.php';
        require_once '../src/views/layouts/main.php';
    }
}