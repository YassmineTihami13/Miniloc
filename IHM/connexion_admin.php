
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion Admin</title>
</head>
<body>
    <h2>Connexion Administrateur</h2>

    <form action="../Traitement/connexion_admin.php" method="POST">
        <label for="email">Email :</label><br>
        <input type="email" id="email" name="email" required><br><br>

        <label for="mot_pass">Mot de passe :</label><br>
        <input type="password" id="mot_pass" name="mot_pass" required><br><br>

        <button type="submit">Se connecter</button>
    </form>
</body>
</html>
