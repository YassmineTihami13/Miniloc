<?php
require_once(__DIR__ . '/../BD/connexion.php');

function verifierCommentairesVisibles($reservation_id) {
    global $conn;
    
    $sql = "SELECT r.*, a.proprietaire_id, a.objet_id 
            FROM reservation r 
            JOIN annonce a ON r.annonce_id = a.id 
            WHERE r.id = ? AND r.statut = 'terminee'";
            
    $stmt = $conn->prepare($sql);
    $stmt->execute([$reservation_id]);
    $reservation = $stmt->fetch();
    
    if (!$reservation) {
        return false;
    }

    $date_fin = new DateTime($reservation['date_fin']);
    $aujourd_hui = new DateTime();
    $date_limite = clone $date_fin;
    $date_limite->modify('+7 days');

    $sql_commentaires = "SELECT evaluateur_id 
                        FROM evaluation 
                        WHERE reservation_id = ? 
                        AND evaluateur_id IN (?, ?)";
                        
    $stmt = $conn->prepare($sql_commentaires);
    $stmt->execute([
        $reservation_id,
        $reservation['client_id'],
        $reservation['proprietaire_id']
    ]);
    $commentaires = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    return (count($commentaires) == 2 || $aujourd_hui >= $date_limite);
}

function getCommentaires($objet_id, $type = 'client') {
    global $conn;
    
    $sql = "SELECT DISTINCT e.*, u.nom, u.prenom, r.id as reservation_id, 
            r.date_fin, r.statut, r.client_id, a.proprietaire_id, a.objet_id
            FROM evaluation e 
            JOIN utilisateur u ON e.evaluateur_id = u.id 
            JOIN reservation r ON e.reservation_id = r.id
            JOIN annonce a ON r.annonce_id = a.id
            WHERE a.objet_id = ? AND e.evaluateur_id " . 
            ($type === 'client' ? "= r.client_id" : "= a.proprietaire_id") . "
            ORDER BY e.date DESC";
            
    $stmt = $conn->prepare($sql);
    $stmt->execute([$objet_id]);
    $commentaires = $stmt->fetchAll();

    $commentairesVisibles = [];
    foreach ($commentaires as $commentaire) {
        if (verifierCommentairesVisibles($commentaire['reservation_id'])) {
            $commentairesVisibles[] = $commentaire;
        }
    }
    
    return $commentairesVisibles;
}

function getCommentairesSurClient($user_id) {
    global $conn;
    
    $sql = "SELECT DISTINCT e.*, u.nom, u.prenom, r.id as reservation_id, 
                   r.date_fin, r.statut, r.client_id, a.proprietaire_id, a.objet_id
            FROM evaluation e 
            JOIN utilisateur u ON e.evaluateur_id = u.id 
            JOIN reservation r ON e.reservation_id = r.id
            JOIN annonce a ON r.annonce_id = a.id
            WHERE e.evalue_id = ?
            AND e.evaluateur_id = a.proprietaire_id
            ORDER BY e.date DESC";
            
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id]);
    $commentaires = $stmt->fetchAll();

    $commentairesVisibles = [];
    foreach ($commentaires as $commentaire) {
        if (verifierCommentairesVisibles($commentaire['reservation_id'])) {
            $commentairesVisibles[] = $commentaire;
        }
    }
    
    return $commentairesVisibles;
}

function getClientInfo($user_id) {
    global $conn; 
    $sql = "SELECT nom, prenom, email,img_profil FROM utilisateur WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC); 
}

function getAllCommentairesWithDetails() {
    global $conn;
    
    try {
        $query = "SELECT e.*, u.nom, u.prenom, u.email, o.nom as objet_nom 
                  FROM evaluation e 
                  JOIN utilisateur u ON e.evaluateur_id = u.id 
                  JOIN objet o ON e.objet_id = o.id 
                  ORDER BY e.date DESC";
                  $stmt = $conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        return [];
    }
}

function deleteCommentaire($comment_id) {
    global $conn;
    
    try {
        $query = "DELETE FROM evaluation WHERE id = ?";
        $stmt = $conn->prepare($query);
        return $stmt->execute([$comment_id]);
    } catch (PDOException $e) {
        error_log("Delete error: " . $e->getMessage());
        return false;
    }
}