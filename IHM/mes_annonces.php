<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit;
}
include_once('../BD/connexion.php');

$user_id = $_SESSION['user_id'];
$aujourdhui = date('Y-m-d');

// Récupérer toutes les réservations sur MES annonces
$query = "
SELECT r.*, a.id as annonce_id, a.proprietaire_id, o.nom as objet_nom, u.nom as client_nom, u.email as client_email
FROM reservation r
JOIN annonce a ON r.annonce_id = a.id
JOIN objet o ON a.objet_id = o.id
JOIN utilisateur u ON r.client_id = u.id
WHERE a.proprietaire_id = :user_id
ORDER BY r.date_debut DESC
";
$stmt = $conn->prepare($query);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$reservations = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Suivre vos annonces - Miniloc</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container py-5">
    <h2>Réservations reçues pour vos annonces</h2>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <table class="table table-bordered align-middle">
        <thead>
            <tr>
                <th>Objet</th>
                <th>Client</th>
                <th>Période</th>
                <th>Option livraison</th>
                <th>Adresse</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($reservations as $res): ?>
            <tr>
                <td><?= htmlspecialchars($res['objet_nom']) ?></td>
                <td>
                    <h1>X</h1>
                </td>
                <td><?= htmlspecialchars($res['date_debut']) ?> au <?= htmlspecialchars($res['date_fin']) ?></td>
                <td><?= htmlspecialchars($res['option_de_livraison']) ?></td>
                <td><?= nl2br(htmlspecialchars($res['address_de_livraison'])) ?></td>
                <td>
                    <?php
                    switch ($res['statut']) {
                        case 'en_attente':
                            echo '<span class="badge bg-warning text-dark">En attente</span>';
                            break;
                        case 'confirmee':
                            echo '<span class="badge bg-success">Confirmée</span>';
                            break;
                        case 'rejete':
                            echo '<span class="badge bg-danger">Rejetée</span>';
                            break;
                        case 'terminee':
                            echo '<span class="badge bg-info">Terminée</span>';
                            break;
                        default:
                            echo htmlspecialchars($res['statut']);
                    }
                    ?>
                </td>
                <td>
                    <?php if ($res['statut'] == 'en_attente'): ?>
                        <form method="post" action="../Traitement/traitement_reservation_action.php" style="display:inline;">
                            <input type="hidden" name="reservation_id" value="<?= $res['id'] ?>">
                            <button type="submit" name="action" value="confirmer" class="btn btn-success btn-sm">
                                <i class="fas fa-check"></i> Confirmer
                            </button>
                        </form>
                        <form method="post" action="../Traitement/traitement_reservation_action.php" style="display:inline;">
                            <input type="hidden" name="reservation_id" value="<?= $res['id'] ?>">
                            <button type="submit" name="action" value="rejeter" class="btn btn-danger btn-sm">
                                <i class="fas fa-times"></i> Rejeter
                            </button>
                        </form>
                    <?php elseif ($res['statut'] == 'confirmee' && $res['date_fin'] <= $aujourdhui): ?>
                        <form method="post" action="../Traitement/traitement_reservation_action.php" style="display:inline;">
                            <input type="hidden" name="reservation_id" value="<?= $res['id'] ?>">
                            <button type="submit" name="action" value="terminer" class="btn btn-primary btn-sm">
                                <i class="fas fa-flag-checkered"></i> Terminer
                            </button>
                        </form>
                    <?php elseif ($res['statut'] == 'terminee'): ?>
                        <span class="badge bg-info">Terminée</span>
                    <?php else: ?>
                        <em>Aucune action</em>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
