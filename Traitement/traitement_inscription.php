
<?php
ob_start();
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
}
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Inclure PHPMailer AVANT tout code d'exécution
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

include __DIR__ . '/../BD/connexion.php';
include __DIR__ . '/../BD/utilisateurBD.php';

function envoyerEmailVerification($email, $nom, $verification_token)
{
    $mail = new PHPMailer(true);

    try {
        // Configuration SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'lamiae.maroun@etu.uae.ac.ma';
        $mail->Password = 'eouo jxub gqfw qfic';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;
        $mail->SMTPDebug = 0; // Activation du mode debug

        // Configuration de l'email
        $mail->setFrom('lamiae.maroun@etu.uae.ac.ma', 'MiniLoc'); // Doit correspondre à l'email SMTP
        $mail->addAddress($email, $nom);

        // Contenu
        $mail->isHTML(true);
        $mail->Subject = 'Verification de votre email';
        $lien_verification = "http://localhost/Miniloc-verificationFiches/verifier.php?token=$verification_token";
        $mail->Body = '
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 20px auto; padding: 30px; border: 1px solid #e0e0e0; border-radius: 10px; }
        .header { text-align: center; padding-bottom: 20px; border-bottom: 2px solid #2196F3; }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background: #2196F3;
            color: white !important;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; text-align: center; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="color: #2196F3; margin: 0">Bienvenue sur MiniLoc 👶</h2>
        </div>
        
        <p>Bonjour ' . htmlspecialchars($nom) . ',</p>
        <p>Merci pour votre inscription! Veuillez cliquer sur le bouton ci-dessous pour activer votre compte :</p>
        
        <p style="text-align: center">
            <a href="' . $lien_verification . '" class="button">
                Activer mon compte
            </a>
        </p>

        <p>Ou copiez ce lien dans votre navigateur :</p>
        <p style="word-break: break-all">' . $lien_verification . '</p>

        <div class="footer">
            <p>Si vous n\'avez pas créé de compte, veuillez ignorer cet email.</p>
            <p>© ' . date('Y') . ' MiniLoc - Tous droits réservés</p>
        </div>
    </div>
</body>
</html>';

        // Ajouter une version texte brut
        $mail->AltBody = "Bonjour $nom,\n\nMerci pour votre inscription! Cliquez sur ce lien pour activer votre compte :\n$lien_verification";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("ERREUR SMTP: " . $mail->ErrorInfo);
        return false;
    }
}

// Vérification que les champs sont bien envoyés et non vides
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = isset($_POST['nom']) ? $_POST['nom'] : '';
    $prenom = isset($_POST['prenom']) ? $_POST['prenom'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $mot_de_passe = isset($_POST['mot_de_passe']) ? password_hash($_POST['mot_de_passe'], PASSWORD_DEFAULT) : '';
    $CIN = isset($_POST['CIN']) ? $_POST['CIN'] : '';
    $address = isset($_POST['address']) ? $_POST['address'] : '';
    $roles = isset($_POST['roles']) ? $_POST['roles'] : [];

    // Vérification que l'utilisateur a accepté les conditions générales
    if (!isset($_POST['accept_conditions'])) {
        echo "Vous devez accepter les conditions générales pour vous inscrire.";
        exit;
    }

    // Si les champs requis sont vides, renvoyer un message d'erreur
    if (empty($nom) || empty($prenom) || empty($email) || empty($mot_de_passe) || empty($CIN) || empty($address)) {
        echo "Tous les champs sont obligatoires.";
        exit;
    }

    // Récupérer les rôles sélectionnés
    $est_client = in_array('client', $roles) ? 1 : 0;
    $est_partenaire = in_array('proprietaire', $roles) ? 1 : 0;

    // Si aucun rôle n'est sélectionné, définir un rôle par défaut (par exemple 'client')
    $role = 'client';
    if (count($roles) == 1) {
        $role = $roles[0];
    }

    // Fonction pour uploader les images
    function uploadImage($fileInputName, $destinationFolder = "../uploads/")
    {
        if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] === 0) {
            $tmp = $_FILES[$fileInputName]['tmp_name'];
            $name = uniqid() . "_" . basename($_FILES[$fileInputName]['name']);
            $dest = $destinationFolder . $name;
            if (!file_exists($destinationFolder)) {
                mkdir($destinationFolder, 0777, true);
            }
            move_uploaded_file($tmp, $dest);
            return $dest;
        } else {
            return null; // Si aucun fichier n'est téléchargé
        }
    }

    // Enregistrer les images dans un dossier spécifique
    $img_profil = uploadImage('img_profil');
    $img_cin_front = uploadImage('img_cin_front');
    $img_cin_back = uploadImage('img_cin_back');

    $verification_token = bin2hex(random_bytes(32));
    // Vérification de la connexion à la base de données
    if (!$conn) {
        echo "Erreur de connexion à la base de données.";
        exit;
    }

    // Appel de la fonction pour insérer l'utilisateur dans la base de données
    $success = insererUtilisateur(
        $nom,
        $prenom,
        $email,
        $mot_de_passe,
        $role,
        $CIN,
        $img_profil,
        $img_cin_front,
        $img_cin_back,
        $address,
        $est_client,
        $est_partenaire,
        $verification_token
    );

    // Si l'inscription est réussie
    if ($success) {
        // Envoyer l'email de vérification
        if (envoyerEmailVerification($email, "$prenom $nom", $verification_token)) {
            ob_end_clean(); // Nettoie le buffer sans l'envoyer
            header("Location: ../IHM/inscription_succes.php?email=" . urlencode($email));
            exit;
        } else {
            echo "Erreur lors de l'envoi de l'email de vérification";
        }
        exit;
    }
}
