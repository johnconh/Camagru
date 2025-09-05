<?php
require_once  '../config/database.php';

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

    
}