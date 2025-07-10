<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Sanitize inputs
    $name     = htmlspecialchars(trim($_POST["name"] ?? ''));
    $surname  = htmlspecialchars(trim($_POST["surname"] ?? ''));
    $email    = filter_var(trim($_POST["email"] ?? ''), FILTER_SANITIZE_EMAIL);
    $sms      = nl2br(htmlspecialchars(trim($_POST["sms"] ?? '')));

    // Validate required fields
    if (empty($name) || empty($surname) || empty($email) || empty($sms)) {
        http_response_code(400);
        echo "Bitte füllen Sie alle Felder aus.";
        exit;
    }

    $subject = "Warteck Kontaktanfrage von $name $surname";
    $mail = new PHPMailer(true);

    try {
        if (!ob_get_level()) ob_start();

        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'presslogic36@gmail.com'; // TODO: Change to Warteck email
        $mail->Password   = 'qsoz cpnl dvwd ibfs'; // Gmail App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->SMTPDebug  = 0;

        $mail->setFrom('no-reply@warteck-badsaeckingen.de', 'Warteck Website');
        $mail->addAddress('warteckbadsaeckingen@gmail.com'); // TODO: Add Warteck/owner email(s)
        $mail->addAddress('presslogic36@gmail.com'); // Optional: backup

        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = $subject;
        $mail->Body = "
            <div style='font-family:sans-serif;max-width:500px;margin:0 auto;'>
                <h2 style='color:#F29E2E;'>Neue Kontaktanfrage von der Warteck Website</h2>
                <p><strong>Name:</strong> $name $surname</p>
                <p><strong>Email:</strong> $email</p>
                <p><strong>Nachricht:</strong><br>$sms</p>
                <hr style='border:0;border-top:1px solid #eee;'>
                <p style='font-size:12px;color:#888;'>Dies ist eine Nachricht vom Kontaktformular auf <a href='https://www.warteck-badsaeckingen.de' style='color:#0A3D2E;'>warteck-badsaeckingen.de</a>.</p>
            </div>
        ";
        $mail->AltBody = "Neue Kontaktanfrage von $name $surname:\nEmail: $email\nNachricht:\n$sms";

        $mail->send();

        if (ob_get_length()) ob_end_clean();
        header("Location: thank-you.html");
        exit();

    } catch (Exception $e) {
        error_log("Mail Error: " . $mail->ErrorInfo);
        if (in_array($_SERVER['SERVER_NAME'], ['localhost', '127.0.0.1'])) {
            echo "Mailer Error: {$mail->ErrorInfo}";
        } else {
            header("Location: error.html");
            exit();
        }
    }

} else {
    header("Location: error.html");
    exit();
}
