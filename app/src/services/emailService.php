<?php
class EmailService {
    public static function sendVerificationEmail($to, $token) {
        $subject = "Verify Your Email Address";
        $link = "http://localhost/index.php?page=verify&token=$token";
        $body = "Hello!\n\nPlease click the link below to verify your account:\n$link\n\nThank you!";
        return mail($to, $subject, $body, "From: no-reply@camagru.com");
    }
}
