<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

function sendEmail($to, $subject, $message) {
    $mail = new PHPMailer(true);
    
    try {
        // Configuration SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Remplace avec ton serveur SMTP
        $mail->SMTPAuth = true;
        $mail->Username = 'sowabou8903@gmail.com'; // Remplace avec ton email
        $mail->Password = 'vczuwruxyybnsdje'; // Remplace avec ton mot de passe
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Destinataire
        $mail->setFrom('sowabou8903@gmail.com', 'DGE Sénégal');
        $mail->addAddress($to);

        // Contenu du mail
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = "<p>$message</p>";

        $mail->send();
    } catch (Exception $e) {
        error_log("Erreur d'envoi d'email: {$mail->ErrorInfo}");
    }
}
?>
