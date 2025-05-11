<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../BD/connexion.php';

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT nom FROM utilisateur WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $user_name = $user ? $user['nom'] : 'Partenaire';
} else {
    $user_name = 'Invité';
}
?>


<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>BabyShop Navbar</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 50px;
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #e91e63;
            margin-right: 40px;
        }

        .nav-section {
            display: flex;
            align-items: center;
            gap: 40px;
            flex-grow: 1;
        }

        .nav-links {
            display: flex;
            gap: 25px;
            list-style: none;
        }

        .nav-links a {
            text-decoration: none;
            color: #2196f3;
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav-links a:hover {
            color: #e91e63;
        }

        .search-bar {
            flex: 0 1 400px;
            margin: 0 20px;
            position: relative;
        }

        .search-bar input {
            width: 100%;
            padding: 8px 15px 8px 35px;
            border: 1px solid #ddd;
            border-radius: 20px;
            font-size: 14px;
        }

        .search-bar i {
            position: absolute;
            top: 50%;
            left: 10px;
            transform: translateY(-50%);
            color: #aaa;
        }

        .auth-buttons {
            display: flex;
            gap: 15px;
            margin-left: auto;
        }

        .auth-buttons a {
            padding: 8px 20px;
            border-radius: 15px;
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .signup {
            background-color: #e91e63;
            color: #fff;
        }

        .login {
            border: 1px solid #2196f3;
            color: #2196f3;
        }

        .welcome-message {
            color: #e91e63;
            /* Couleur rose coordonnée au logo */
            font-weight: 500;
            margin-right: 25px;
            font-size: 16px;
            letter-spacing: 0.5px;
            padding: 6px;
            border-radius: 20px;
            background-color: rgba(233, 30, 99, 0.1);
            /* Fond semi-transparent */
            transition: all 0.3s ease;
        }

        @media (max-width: 1200px) {
            .navbar {
                padding: 15px 20px;
            }

            .nav-links {
                display: none;
            }

            .search-bar {
                flex: 1;
            }
        }


        /* Style des boutons */
        .auth-buttons a {
            padding: 8px 15px;
            border-radius: 20px;
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            border: 2px solid transparent;
            background: #f8f9fa;
            color: #2196F3;
            margin-left: 10px;
        }

        /* Déconnexion */
        .logout {
            color: #e91e63 !important;
            border-color: #e91e63;
        }



        /* Boutons switch */
        .btn-switch,
        .devenir-role {
            border-color: #2196F3;
            color: #2196F3 !important;
        }

        

        
        .welcome-message {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    text-align: center;
    border-radius: 50px;
    background: rgba(233, 30, 99, 0.05);
    border: 1px solid rgba(233, 30, 99, 0.15);
    padding-left: 15px;
    
}
    </style>
</head>

<body>
    <nav class="navbar">
        <div class="nav-left">
            <div class="logo"><i class="fa-solid fa-baby"></i> BabyShop</div>
            <span class="welcome-message">
            <i class="fa-regular fa-user"></i><?php echo htmlspecialchars($user_name); ?>
            </span>
        </div>
        <ul class="nav-links">

            <?php
            if (isset($_SESSION['user_id'])) {
                // Rôle CLIENT
                if ($_SESSION['role'] === 'client') {
                    echo '<li><a href="../Traitement/traitement_index.php"><i class="fa-solid fa-heart"></i> Acceuil</a></li>';
                    echo '<li><a href="../IHM/produits.php"><i class="fas fa-bullhorn"></i> Annonces</a></li>';
                }
                // Rôle PROPRIETAIRE
                elseif ($_SESSION['role'] === 'proprietaire') {
                    echo '<li><a href="../IHM/espace_partenaire.php"><i class="fa-solid fa-heart"></i> Acceuil</a></li>';
                    echo '<li><a href="../IHM/liste_annonces.php"><i class="fas fa-bullhorn"></i> Mes Annonces</a></li>';
                }
            } else {
                // Liens par défaut si non connecté
                echo '<li><a href="../Traitement/traitement_index.php"><i class="fa-solid fa-heart"></i> Acceuil</a></li>';
                echo '<li><a href="../IHM/produits.php"><i class="fa-solid fa-gift"></i> Annonces</a></li>';
            }
            ?>

        </ul>

        <div class="auth-buttons">
            <?php

            if (isset($_SESSION['user_id'])) {
                // Connexion établie, on récupère les rôles
                $isClient = $_SESSION['is_client'] ?? 0;
                $isPartenaire = $_SESSION['is_partenaire'] ?? 0;

                // S'il n'est que client
                if ($isClient && !$isPartenaire) {
                    echo '<a href="#" class="btn devenir-role" data-role="partenaire"><i class="fa-solid fa-briefcase"></i> Devenir partenaire</a>';
                } elseif (!$isClient && $isPartenaire) {
                    echo '<a href="#" class="btn devenir-role" data-role="client"><i class="fa-solid fa-user"></i> Devenir client</a>';
                } elseif ($isClient && $isPartenaire) {
                    // Détermination du libellé dynamique
                    $currentRole = $_SESSION['role'];
                    $targetRole = ($currentRole === 'client') ? 'Partenaire' : 'Client';
                    $targetIcon = ($currentRole === 'client') ? 'fa-repeat' : 'fa-repeat';

                    echo '<a href="../Traitement/switch_role.php" class="btn btn-switch">';
                    echo '<i class="fas fa-repeat ' . $targetIcon . ' me-2"></i>';
                    echo  $targetRole;
                    echo '</a>';
                }
                // Bouton de déconnexion
                echo '<a href="../Traitement/deconnexion.php" class="logout"><i class="fa-solid fa-right-from-bracket"></i> Déconnexion</a>';
            } else {
                // Utilisateur non connecté
                echo '<a href="../IHM/inscription.php" style="background-color: #e91e63; color: #fff; padding: 8px 20px; border-radius: 15px; text-decoration: none; font-weight: 500; display: flex; align-items: center; gap: 8px;"><i class="fa-solid fa-user-plus"></i> S\'inscrire</a>';
                echo '<a href="../IHM/connexion.php" class="login"><i class="fa-solid fa-right-to-bracket"></i> Connexion</a>';
            }
            ?>


        </div>

    </nav>
    <!-- Modal Conditions -->
    <div class="modal fade" id="conditionsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="conditionsTitle">Conditions Générales</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="conditionsContent">
                    <p>Veuillez lire et accepter nos conditions générales avant de continuer.</p>
                    <div id="lienConditions" style="margin-bottom: 10px;">
                        <!-- Le lien va être ajouté ici dynamiquement -->
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="acceptConditions">
                        <label class="form-check-label" for="acceptConditions">
                            J'accepte les conditions générales
                        </label>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <button type="button" class="btn btn-primary" id="confirmConditions">Accepter</button>
                </div>
            </div>
        </div>
    </div>

</body>

<script>
    let roleToBecome = '';

    document.querySelectorAll('.devenir-role').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            roleToBecome = this.getAttribute('data-role');

            // Remplir seulement le lien et le texte
            const lienConditions = document.getElementById('lienConditions');
            const conditionsTitle = document.getElementById('conditionsTitle');
            const acceptConditionsLabel = document.querySelector('label[for="acceptConditions"]');

            if (roleToBecome === 'client') {
                conditionsTitle.innerText = 'Devenir Client';
                lienConditions.innerHTML = '<a href="../IHM/conditions_client.php" target="_blank">Lire les conditions pour devenir client</a>';
                acceptConditionsLabel.innerText = "J'accepte les conditions générales pour devenir client";
            } else if (roleToBecome === 'partenaire') {
                conditionsTitle.innerText = 'Devenir Partenaire';
                lienConditions.innerHTML = '<a href="../IHM/conditions_partenaire.php" target="_blank">Lire les conditions pour devenir partenaire</a>';
                acceptConditionsLabel.innerText = "J'accepte les conditions générales pour devenir partenaire";
            }

            // Ouvrir le modal
            var modal = new bootstrap.Modal(document.getElementById('conditionsModal'));
            modal.show();
        });
    });

    // Validation du bouton "Accepter"
    document.getElementById('confirmConditions').addEventListener('click', function() {
        if (!document.getElementById('acceptConditions').checked) {
            alert('Veuillez accepter les conditions pour continuer.');
            return;
        }

        // Envoi AJAX
        let url = (roleToBecome === 'client') ? '../Traitement/devenir_client.php' : '../Traitement/devenir_partenaire.php';

        fetch(url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(data => {
                window.location.reload();
            })
            .catch(error => console.error('Erreur:', error));
    });
</script>
<script>
    document.querySelector('.btn-switch').addEventListener('click', function(e) {
        e.preventDefault();

        fetch(this.href, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Redirection immédiate + rechargement du DOM
                    window.location.href = data.redirectUrl;
                } else {
                    alert(data.message);
                }
            })
            .catch(error => console.error('Erreur:', error));
    });
</script>

</html>