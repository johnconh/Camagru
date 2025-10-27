<?php
require_once  __DIR__.'/../config/database.php';

class User{
    public $id;
    public $username;
    public $email;
    public $password;

    public static function register($username, $email, $password) {
        $db = Database::getConnection();
        $stmt = $db->prepare("INSERT INTO users (username, email, password, verification_token) VALUES (:username, :email, :password, :token)");
        $hash= password_hash($password, PASSWORD_BCRYPT);
        $token = bin2hex(random_bytes(16));

        $success = $stmt->execute([
            ':username' => htmlspecialchars($username, ENT_QUOTES, 'UTF-8'),
            ':email' => htmlspecialchars($email, ENT_QUOTES, 'UTF-8'),
            ':password' => $hash,
            ':token' => $token
        ]);

        return $success ? $token : false;
    }

    public static function login($username, $password) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE username = :username AND is_verified = 1");
        $stmt->execute([':username' => htmlspecialchars($username, ENT_QUOTES, 'UTF-8')]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }

        return false;
    }

    public static function findByID ($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public static function verifyToken($token) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT id FROM users WHERE verification_token = :token");
        $stmt->execute([':token' => $token]);
        $user = $stmt->fetch();

        if ($user){
            $stmt = $db->prepare("UPDATE users SET is_verified = 1, verification_token = NULL WHERE id = :id");
            $stmt->execute([':id' => $user['id']]);
            return true;
        }

        return false;
    }
}
