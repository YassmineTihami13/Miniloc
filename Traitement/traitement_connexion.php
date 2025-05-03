<?php
session_start();
include '../BD/utilisateurBD.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST['email'] ?? '';
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';



    if (empty($email) || empty($mot_de_passe)) {
        echo "Veuillez remplir tous les champs.";
        exit;
    }

    $utilisateur = getUtilisateurParEmail($email);

    if ($utilisateur && password_verify($mot_de_passe, $utilisateur['mot_de_passe'])) {
        // Authentification réussie
        $_SESSION['user_id'] = $utilisateur['id'];
        $_SESSION['user_email'] = $utilisateur['email'];
        $_SESSION['user_role'] = $utilisateur['role'];

        $_SESSION['is_client'] = $utilisateur['est_client'];
        $_SESSION['is_partenaire'] = $utilisateur['est_partenaire'];
        header("Location: ../IHM/index.php"); // redirection vers la page d'accueil
        exit;
    } else {
        echo "Email ou mot de passe incorrect.";
    }
}
