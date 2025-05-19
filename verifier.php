<?php
session_start();
include __DIR__ . '/BD/connexion.php';

if (!empty($_GET['token'])) {
    $token = $_GET['token'];
    
    $stmt = $conn->prepare("SELECT id FROM utilisateur WHERE verification_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if ($user) {
        $stmt = $conn->prepare("UPDATE utilisateur SET is_verified = 1, verification_token = NULL WHERE id = ?");
        if ($stmt->execute([$user['id']])) {
            $message = "Email vérifié avec succès! Vous pouvez maintenant vous connecter.";
        } else {
            $message = "Erreur lors de la vérification";
        }
    } else {
        $message = "Token invalide ou expiré";
    }
} else {
    $message = "Token manquant";
}


?>
<div class="form-container">
    <h2>Vérification d'email</h2>
    <p><?= $message ?></p>
    <a href="IHM/connexion.php" class="btn">Se connecter</a>
</div>
