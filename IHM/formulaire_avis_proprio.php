<?php
// Suppression de toute gestion de session

include_once('../BD/connexion.php');

// Vérification de la réservation
if (!isset($_GET['reservation_id']) || !is_numeric($_GET['reservation_id'])) {
    die('Réservation non spécifiée ou invalide.');
}
$reservation_id = (int)$_GET['reservation_id'];

// On récupère la réservation sans filtrer sur le propriétaire
$stmt = $conn->prepare("SELECT r.*, o.nom as objet_nom, a.proprietaire_id 
    FROM reservation r
    JOIN annonce a ON r.annonce_id = a.id
    JOIN objet o ON a.objet_id = o.id
    WHERE r.id = :id");
$stmt->bindParam(':id', $reservation_id, PDO::PARAM_INT);
$stmt->execute();
$res = $stmt->fetch();

if (!$res) {
    echo "<div class='alert alert-danger'>Réservation invalide ou introuvable.</div>";
    exit;
}

// Traitement du formulaire
$success = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $note = (int)$_POST['note'];
    $commentaire = trim($_POST['commentaire']);
    // Enregistrer l'avis dans la table avis_proprio (à créer)
    $stmt = $conn->prepare("INSERT INTO avis_proprio (reservation_id, proprio_id, note, commentaire, date_avis)
                            VALUES (:reservation_id, :proprio_id, :note, :commentaire, NOW())");
    $stmt->execute([
        ':reservation_id' => $reservation_id,
        ':proprio_id' => $res['proprietaire_id'], // On prend le proprio de la réservation trouvée
        ':note' => $note,
        ':commentaire' => $commentaire
    ]);
    $success = "Merci pour votre retour !";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Avis sur le client</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container py-5">
    <h2>Laisser un avis sur le client de la réservation : <?= htmlspecialchars($res['objet_nom']) ?></h2>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php else: ?>
    <form method="post">
        <div class="mb-3">
            <label for="note" class="form-label">Note (1 à 5)</label>
            <select class="form-select" name="note" id="note" required>
                <option value="">Choisir...</option>
                <?php for ($i=1; $i<=5; $i++): ?>
                    <option value="<?= $i ?>"><?= $i ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="commentaire" class="form-label">Commentaire</label>
            <textarea class="form-control" name="commentaire" id="commentaire" rows="4" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Envoyer mon avis</button>
    </form>
    <?php endif; ?>
</div>
</body>
</html>
