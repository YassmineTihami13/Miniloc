<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

function envoyerEmailVerification($email, $nom, $verification_token) {
    $mail = new PHPMailer(true);
    
    try {
        // Configuration SMTP (À MODIFIER AVEC VOS COORDONNÉES)
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Serveur SMTP
        $mail->SMTPAuth = true;
        $mail->Username = 'lamiaemaroun@gmail.com'; // Votre email
        $mail->Password = 'Lm202680_app'; // Mot de passe d'application
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; 
        $mail->Port = 465;

        // Destinataire
        $mail->setFrom('noreply@example.com', 'MiniLoc');
        $mail->addAddress($email, $nom);

        // Contenu
        $mail->isHTML(true);
        $mail->Subject = 'Verification de votre email';
        
        $lien_verification = "http://localhost/Miniloc-verificationFiches/verifier.php?token=$verification_token";
        
        $mail->Body = "
            <h1>Merci pour votre inscription!</h1>
            <p>Veuillez cliquer sur le lien pour vérifier votre email :</p>
            <a href='$lien_verification'>Vérifier mon compte</a>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Erreur d'envoi d'email: " . $mail->ErrorInfo);
        return false;
    }
}
?>