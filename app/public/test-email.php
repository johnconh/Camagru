<?php
$to_email = $_ENV['TEST_EMAIL'];
$smtp_host = $_ENV['MAIL_HOST'];
$smtp_port = $_ENV['MAIL_PORT'];
$smtp_user = $_ENV['MAIL_USERNAME'];
$smtp_pass = $_ENV['MAIL_PASSWORD'];

echo "<h2>Test Email</h2>";
echo "<p>Enviando email a: <strong>$to_email</strong></p>";

if (empty($smtp_host) || empty($smtp_port) || empty($smtp_user) || empty($smtp_pass)) {
    echo "<p style='color: red;'>Error: Las variables de entorno para la configuración SMTP no están completamente definidas.</p>";
    exit;
}

try {
    $result = sendGmailTest($smtp_host, $smtp_port, $smtp_user, $smtp_pass, $to_email);
    if ($result) {
        echo "<p style='color: green;'>Éxito: El email de prueba se envió correctamente.</p>";
    } else {
        echo "<p style='color: red;'>Error: No se pudo enviar el email de prueba.</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Error al enviar el email de prueba: " . $e->getMessage() . "</p>";
}

function smtp_get_lines($socket) {
    $lines = [];
    while ($line = fgets($socket, 4096)) {
        $line = trim($line);
        $lines[] = $line;
        echo "RESPUESTA SMTP: $line<br>";
        // Si la línea no tiene '-' después del código (250-), termina la respuesta
        if (substr($line, 3, 1) != '-') {
            $lines[] = "\r\n";
            break;
        }
    }
    echo "<br>";
    return $lines;
}

function sendGmailTest($host, $port, $username, $password, $to_email) {

    $subject = " Test Email esta funcionando";
    $body = "¡Hola!\n\nSi recibes este email, Gmail está configurado correctamente para Camagru.\n\nEnviado el: " . date('Y-m-d H:i:s');

    $socket = fsockopen($host, $port, $errno, $errstr, 10);
    if (!$socket) {
        throw new Exception("No se pudo conectar al servidor SMTP: $errstr ($errno)");
    }

    fgets($socket, 4096);
    fwrite($socket, "EHLO localhost\r\n");
    smtp_get_lines($socket);
    fwrite($socket, "STARTTLS\r\n");
    $resp=fgets($socket, 4096);
    if (strpos($resp, "220") === false) {
        throw new Exception("El servidor no acepto STARTTLS: $resp");
    }

    if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)){
        throw new Exception("No se pudo iniciar TLS");
    }

    fwrite($socket, "EHLO  localhost \r\n");
    smtp_get_lines($socket);
    fwrite($socket, "AUTH LOGIN\r\n");
    fgets($socket, 4096);
    fwrite($socket, base64_encode($username) . "\r\n");
    fgets($socket, 4096);
    fwrite($socket, base64_encode($password) . "\r\n");
    $auth_response = fgets($socket, 4096);
    if (strpos($auth_response, "235") === false) {
        throw new Exception("Autenticación fallida: $auth_response");
    }

    fwrite($socket, "MAIL FROM: <$username>\r\n");
    fgets($socket, 4096);
    fwrite($socket, "RCPT TO: <$to_email>\r\n");
    fgets($socket, 4096);
    fwrite($socket, "DATA\r\n");
    fgets($socket, 4096);

    $email = "Subject: $subject\r\n";
    $email .= "From: $username\r\n";
    $email .= "To: $to_email\r\n";
    $email .= $body;
    $email .= "\r\n.\r\n";

    fwrite($socket, $email);
    $send_response = fgets($socket, 4096);

    fwrite($socket, "QUIT\r\n");
    fclose($socket);

    return strpos($send_response, "250") !== false;
}

echo "<hr>";
echo "<p>Configuración usada:</p>";
echo "<ul>";
echo "<li>Host: $smtp_host</li>";
echo "<li>Puerto: $smtp_port</li>";
echo "<li>Usuario: $smtp_user</li>";
echo "<li>Contraseña: " . (empty($smtp_pass) ? 'NO configurada' : 'Configurada ✅') . "</li>";
echo "</ul>";
?>