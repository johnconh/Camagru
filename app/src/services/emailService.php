<?php
class EmailService {
    public static function sendVerificationEmail($to, $token) {
        $smtp_host = $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com';
        $smtp_port = $_ENV['MAIL_PORT'] ?? 587;
        $smtp_user = $_ENV['MAIL_USERNAME'] ?? '';
        $smtp_pass = $_ENV['MAIL_PASSWORD'] ?? '';
        $app_url = $_ENV['APP_URL'] ?? 'http://localhost:8080';


        $subject = "Verify Your Email Address";
        $link = "$app_url/index.php?page=verify&token=$token";
        $body = "Hello!\n\nPlease click the link below to verify your account:\n$link\n\nThank you!";

        try {
            return self::sendSMTP($smtp_host, $smtp_port, $smtp_user, $smtp_pass, $to, $subject, $body);
        } catch (Exception $e) {
            error_log("Error sending verification email: " . $e->getMessage());
            return false;
        }
    }

    private static function sendSMTP($host, $port, $username, $password, $to, $subject, $body) {

        $socket = fsockopen($host, $port, $errno, $errstr, 30);
        if (!$socket) {
            throw new Exception("Could not connect to SMTP: $errstr ($errno)");
        }
        
        fgets($socket, 4096);

        fwrite($socket, "EHLO localhost\r\n");
        self::readResponse($socket);
        
        fwrite($socket, "STARTTLS\r\n");
        $resp = fgets($socket, 4096);
        if (strpos($resp, "220") === false) {
            fclose($socket);
            throw new Exception("The server did not accept STARTTLS: $resp");
        }
        
        if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            fclose($socket);
            throw new Exception("Could not start TLS");
        }

        fwrite($socket, "EHLO localhost\r\n");
        self::readResponse($socket);

        fwrite($socket, "AUTH LOGIN\r\n");
        fgets($socket, 4096);

        fwrite($socket, base64_encode($username) . "\r\n");
        fgets($socket, 4096);
        
        fwrite($socket, base64_encode($password) . "\r\n");
        $auth_response = fgets($socket, 4096);
        
        if (strpos($auth_response, "235") === false) {
            fclose($socket);
            throw new Exception("SMTP authentication failed");
        }
        
        fwrite($socket, "MAIL FROM: <$username>\r\n");
        fgets($socket, 4096);

        fwrite($socket, "RCPT TO: <$to>\r\n");
        fgets($socket, 4096);
        
        fwrite($socket, "DATA\r\n");
        fgets($socket, 4096);

        $email = "Subject: $subject\r\n";
        $email .= "From: Camagru <$username>\r\n";
        $email .= "To: $to\r\n";
        $email .= "MIME-Version: 1.0\r\n";
        $email .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $email .= "\r\n";
        $email .= $body;
        $email .= "\r\n.\r\n";
        
        fwrite($socket, $email);
        $send_response = fgets($socket, 4096);
        
        fwrite($socket, "QUIT\r\n");
        fclose($socket);
        
        return strpos($send_response, "250") !== false;
    }
    
    private static function readResponse($socket) {
        $response = '';
        while ($line = fgets($socket, 4096)) {
            $response .= $line;
            if (substr($line, 3, 1) != '-') {
                break;
            }
        }
        return $response;
    }
}
