<?php

include_once('../BD/connexion.php');

if (isset($_GET['id']) ) {
    $id = intval($_GET['id']);
    $note_moyenne = (isset($_GET['note']) && is_numeric($_GET['note'])) ? floatval($_GET['note']) : null;
    $stmt = $conn->prepare("
        SELECT 
            a.adress,
            o.ville,
            o.etat,
            o.nom,
            o.description,
            o.proprietaire_id,
            o.prix_journalier
        FROM annonce a
        JOIN objet o ON a.objet_id = o.id
        WHERE a.id = ?
    ");

    $stmt->execute([$id]);
    $details = $stmt->fetchAll();
    
    $stmt = $conn->prepare("
    SELECT objet_id FROM annonce a WHERE a.id = ?
    ");
    $stmt->execute([$id]);
    $objet = $stmt->fetch(); 
    $objet_id = $objet['objet_id']; 
    
    $stmt = $conn->prepare("
    SELECT url FROM image  WHERE objet_id= ?
    ");
    $stmt->execute([$objet_id]);
    $image = $stmt->fetch(); 
    $image_url = $image ? $image['url'] : null;
    

    
    $stmt = $conn->prepare("
    SELECT * FROM evaluation WHERE objet_id = ?
    ");
    $stmt->execute([$objet_id]);
    $evaluation = $stmt->fetchAll();
    

    $stmt = $conn->prepare("
    SELECT count(*) as number FROM annonce WHERE objet_id = ?
    ");
    $stmt->execute([$objet_id]);
    $nbr_annonce = $stmt->fetch();
    $nbr_publication=$nbr_annonce['number'];


    $stmt = $conn->prepare("
    SELECT nom,prenom,email, img_profil FROM utilisateur WHERE id = ?
    ");
    $stmt->execute([$details[0]['proprietaire_id']]);
    $proprietaire = $stmt->fetch();
    
    $_SESSION['proprietaire'] = $proprietaire;
    $_SESSION['note'] = $note_moyenne ;
    $_SESSION['nbr_annonce'] = $nbr_publication-1 ;
    $_SESSION['details'] = $details;
    $_SESSION['objet_id'] = $objet_id;
    $_SESSION['evaluation'] = $evaluation;
    $_SESSION['image'] = $image_url;
    if (!$details) {
        echo "Annonce non trouvée.";
        exit;
    }

    
} else {
    echo "Aucune annonce sélectionnée.";
    exit;
}
?>
