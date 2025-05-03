<?php
session_start();
include ('../Traitement/detailsAnnonce.php'); 

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails du Produit</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
   
</head>
<style>
    body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

h2, h4 {
    color: #333;
}

.btn-primary {
    background-color: #007bff;
    border: none;
}

.btn-primary:hover {
    background-color: #0056b3;
}

</style>
<body class="bg-light">

<div class="container py-5">
    <div class="row">
        <div class="col-md-6 text-center">
            <img id="product-image" src="../photos/<?= htmlspecialchars($_SESSION['image']) ?>" alt="Produit" class="img-fluid rounded shadow">
        </div>
        <div class="col-md-6">
            <h2 id="product-name" class="fw-bold mb-3"><?= htmlspecialchars($_SESSION['details'][0]['nom']) ?></h2>
            <h4 id="product-price" class="text-success mb-4"><?= htmlspecialchars($_SESSION['details'][0]['prix_journalier']) ?> Dh/jour</h4>
            <p id="product-description" >
            <strong>Description de l'objet:</strong> <?= htmlspecialchars($_SESSION['details'][0]['description']) ?>
            </p>
            <p><strong>Adresse de l'objet :</strong> <?= htmlspecialchars($_SESSION['details'][0]['ville']) ?></p>
            <p><strong>Adresse de l'annonce :</strong> <?= htmlspecialchars($_SESSION['details'][0]['adress']) ?></p>
            <p><strong>Status de l'objet :</strong> <?= htmlspecialchars($_SESSION['details'][0]['etat']) ?></p>
            <p><strong>Nombre de location précédente :</strong> <?= isset($_SESSION['nbr_annonce']) && $_SESSION['nbr_annonce'] !== null  ? $_SESSION['nbr_annonce']  : 'Cest la prmière publication de cet objet' ?></p>
            <p><strong>Évaluation de l'objet :</strong> 
                <?= isset($_SESSION['note']) && $_SESSION['note'] !== null 
                    ? $_SESSION['note'] . ' ⭐' 
                    : 'Pas encore noté' ?>
            </p>
            <p><strong>Commentaire:</strong></p>
            <?php foreach ($evaluation as $eval): ?>
                
                <h6><?php echo $eval['commentaire']; ?></h6>
            <?php endforeach; ?>
            <p>
            <strong>Partenaire :</strong>
            <a href="#" data-bs-toggle="modal" data-bs-target="#partenaireModal">
                Voir le profil du partenaire
            </a>
           </p>
           <!-- Utiliser directement l'ID de l'annonce stocké dans la session -->
           <a href="../IHM/formulaire_reservation.php?annonce_id=<?= $_SESSION['annonce_id'] ?>" class="btn btn-primary me-2">Réserver</a>
           <button class="btn btn-outline-secondary" onclick="goBack()">Retour</button>
        </div>
    </div>
</div>

<div class="modal fade" id="partenaireModal" tabindex="-1" aria-labelledby="partenaireModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="partenaireModalLabel">Fiche du Partenaire</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body">
        <img src="<?= htmlspecialchars($_SESSION['proprietaire']['img_profil']) ?>" alt="Profil partenaire" 
        class="rounded-circle mb-3 d-block mx-auto" 
        style="width: 120px; height: 120px; object-fit: cover;">
        <h6 class="text-center"><?= htmlspecialchars($_SESSION['proprietaire']['nom']) ?> <?= htmlspecialchars($_SESSION['proprietaire']['prenom']) ?></h6>
        
        <p><strong>Email:</strong> <?= htmlspecialchars($_SESSION['proprietaire']['email']) ?></p>
        <p><strong>Évaluations :</strong> je vais metter ici la moyenne de la note pris par cette propriètaire dans la table évaluation</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
      </div>
    </div>
  </div>
</div>

<script>
  document.getElementById('poussette-card').addEventListener('click', function () {
    const modal = new bootstrap.Modal(document.getElementById('poussetteModal'));
    modal.show();
  });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function goBack() {
        window.history.back();
    }
</script>

</body>
</html>