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

    public static function createResetToken($email) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT id FROM users WHERE email = :email AND is_verified = 1");
        $stmt->execute([':email' => htmlspecialchars($email, ENT_QUOTES, 'UTF-8')]);
        $user = $stmt->fetch();

        if ($user) {
            $token = bin2hex(random_bytes(16));
            $expires = date('Y-m-d H:i:s', strtotime('+5 minutes'));

            $stmt = $db->prepare("UPDATE users SET reset_token = :token, reset_token_expires = :expires WHERE id = :id");
            $stmt->execute([
                ':token' => $token,
                ':expires' => $expires,
                ':id' => $user['id']
            ]);

            return $token;
        }

        return false;
    }

    public static function verifyResetToken($token) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT id, email, reset_token_expires FROM users WHERE reset_token = :token");
        $stmt->execute([':token' => $token]);
        $user = $stmt->fetch();

        if(!$user) {
            return false;
        }

        $now = new DateTime();
        $expires = new DateTime($user['reset_token_expires']);

        if ($now > $expires) {
            self::clearResetToken($user['id']);
            return false;
        }

        return $user;
    }

    public static function resetPassword($token, $newPassword) {
        $user = self::verifyResetToken($token);

        if (!$user) {
            return false;
        }

        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE users SET password = :password, reset_token = NULL, reset_token_expires = NULL WHERE id = :id");
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);

        return $stmt->execute([
            ':password' => $hash,
            ':id' => $user['id']
        ]);
    }

    private static function clearResetToken($userId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE users SET reset_token = NULL, reset_token_expires = NULL WHERE id = :id");
        $stmt->execute([':id' => $userId]);
    }

}
