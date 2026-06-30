<?php
session_start();

// SÉCURITÉ — Même logique que pour le dashboard client, mais inversée :
// seul un ouvrier connecté peut accéder à cette page.
if (!isset($_SESSION['type_utilisateur']) || $_SESSION['type_utilisateur'] !== 'ouvrier') {
    header("Location: ../auth/connexion.php");
    exit;
}

$nomOuvrier = $_SESSION['nom'] ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/accueil.css">
    <title>Accueil</title>
</head>
<body>
   <header>
    <div class="logo">
        <img src="" alt="" >
        <h2>JobLink</h2>
    </div>
    <div class="listes">
        <ul>
            <li><a href="">Accueil</a></li>
            <li><a href="">Categories</a></li>
            <li><a href="">Profils</a></li>
        </ul>
    </div>
    <div class="btn">
        <a href="../auth/Deconnexion.php">Deconnexion</a>
    </div>
   </header>

   <main>
     <p>Bienvenue, Ouvrier <?php echo htmlspecialchars($nomOuvrier); ?> !</p>
   </main>
</body>
</html>