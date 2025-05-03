<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Vous devez être connecté pour effectuer une réservation.";
    header('Location: ../IHM/connexion.php');
    exit;
}

// Vérifier que la méthode est POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../IHM/produits.php');
    exit;
}

include_once('../BD/connexion.php');

// Récupérer les données du formulaire
$annonce_id = isset($_POST['annonce_id']) ? (int)$_POST['annonce_id'] : 0;
$date_debut = isset($_POST['date_debut']) ? $_POST['date_debut'] : '';
$date_fin = isset($_POST['date_fin']) ? $_POST['date_fin'] : '';
$option_de_livraison = isset($_POST['option_de_livraison']) ? $_POST['option_de_livraison'] : '';
$address_de_livraison = isset($_POST['address_de_livraison']) ? $_POST['address_de_livraison'] : '';
$client_id = $_SESSION['user_id'];

// Validation des données
if (!$annonce_id || !$date_debut || !$date_fin || !$option_de_livraison) {
    $_SESSION['error'] = "Tous les champs obligatoires doivent être remplis.";
    header('Location: ../IHM/formulaire_reservation.php?annonce_id=' . $annonce_id);
    exit;
}

// Vérifier que la date de début est avant la date de fin
if (strtotime($date_debut) > strtotime($date_fin)) {
    $_SESSION['error'] = "La date de début doit être antérieure à la date de fin.";
    header('Location: ../IHM/formulaire_reservation.php?annonce_id=' . $annonce_id);
    exit;
}

// Vérification de l'adresse si option de livraison à domicile
if ($option_de_livraison === 'domicile' && empty($address_de_livraison)) {
    $_SESSION['error'] = "L'adresse de livraison est obligatoire pour la livraison à domicile.";
    header('Location: ../IHM/formulaire_reservation.php?annonce_id=' . $annonce_id);
    exit;
}

try {
    // Vérifier que l'annonce existe et est disponible
    $query = "SELECT a.*, o.nom as objet_nom, o.id as objet_id, a.proprietaire_id 
              FROM annonce a 
              JOIN objet o ON a.objet_id = o.id 
              WHERE a.id = :annonce_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':annonce_id', $annonce_id, PDO::PARAM_INT);
    $stmt->execute();
    $annonce = $stmt->fetch();

    if (!$annonce) {
        $_SESSION['error'] = "L'annonce demandée n'existe pas.";
        header('Location: ../IHM/produits.php');
        exit;
    }

    if ($annonce['statut'] !== 'disponible') {
        $_SESSION['error'] = "Cette annonce n'est pas disponible à la location actuellement.";
        header('Location: ../IHM/produits.php');
        exit;
    }

    // Vérifier que la période demandée est dans la plage de disponibilité de l'annonce
    if (strtotime($date_debut) < strtotime($annonce['date_debut']) || 
        strtotime($date_fin) > strtotime($annonce['date_fin'])) {
        $_SESSION['error'] = "Les dates sélectionnées ne sont pas dans la période de disponibilité de l'annonce.";
        header('Location: ../IHM/formulaire_reservation.php?annonce_id=' . $annonce_id);
        exit;
    }

    // Vérifier que la période n'est pas déjà réservée
    $query_check = "SELECT COUNT(*) FROM reservation 
                    WHERE annonce_id = :annonce_id 
                    AND statut != 'annulee'
                    AND (
                        (date_debut <= :date_debut AND date_fin >= :date_debut) OR
                        (date_debut <= :date_fin AND date_fin >= :date_fin) OR
                        (date_debut >= :date_debut AND date_fin <= :date_fin)
                    )";
    $stmt_check = $conn->prepare($query_check);
    $stmt_check->bindParam(':annonce_id', $annonce_id, PDO::PARAM_INT);
    $stmt_check->bindParam(':date_debut', $date_debut);
    $stmt_check->bindParam(':date_fin', $date_fin);
    $stmt_check->execute();
    
    if ($stmt_check->fetchColumn() > 0) {
        $_SESSION['error'] = "La période sélectionnée n'est pas disponible. Veuillez choisir d'autres dates.";
        header('Location: ../IHM/formulaire_reservation.php?annonce_id=' . $annonce_id);
        exit;
    }

    // Empêcher un propriétaire de réserver sa propre annonce
    if ($client_id == $annonce['proprietaire_id']) {
        $_SESSION['error'] = "Vous ne pouvez pas réserver votre propre annonce.";
        header('Location: ../IHM/produits.php');
        exit;
    }

    // Insertion de la réservation
    $query_insert = "INSERT INTO reservation (client_id, annonce_id, date_debut, date_fin, statut, option_de_livraison, address_de_livraison) 
                     VALUES (:client_id, :annonce_id, :date_debut, :date_fin, 'en_attente', :option_de_livraison, :address_de_livraison)";
    $stmt_insert = $conn->prepare($query_insert);
    $stmt_insert->bindParam(':client_id', $client_id, PDO::PARAM_INT);
    $stmt_insert->bindParam(':annonce_id', $annonce_id, PDO::PARAM_INT);
    $stmt_insert->bindParam(':date_debut', $date_debut);
    $stmt_insert->bindParam(':date_fin', $date_fin);
    $stmt_insert->bindParam(':option_de_livraison', $option_de_livraison);
    $stmt_insert->bindParam(':address_de_livraison', $address_de_livraison);
    $stmt_insert->execute();
    
    $reservation_id = $conn->lastInsertId();
    
    // Ajouter un message de succès et rediriger
    $_SESSION['success'] = "Votre réservation a été confirmée avec succès ! Elle est maintenant en attente de validation par le propriétaire.";
    header('Location: ../IHM/formulaire_reservation.php?annonce_id=' . $annonce_id);
    exit;

} catch (PDOException $e) {
    // Log l'erreur et afficher un message générique
    error_log("ERREUR RÉSERVATION: " . $e->getMessage());
    $_SESSION['error'] = "Une erreur est survenue lors de la réservation. Veuillez réessayer plus tard.";
    header('Location: ../IHM/formulaire_reservation.php?annonce_id=' . $annonce_id);
    exit;
}
?>