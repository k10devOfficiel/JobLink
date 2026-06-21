<?php
session_start();

// SÉCURITÉ — Protection de la page : seul un client connecté peut
// accéder à ce tableau de bord. Si la session est absente ou si elle
// correspond à un ouvrier, on renvoie directement vers la connexion
// (empêche par exemple un ouvrier de taper l'URL du dashboard client
// à la main pour y accéder).
if (!isset($_SESSION['type_utilisateur']) || $_SESSION['type_utilisateur'] !== 'client') {
    header("Location: ../auth/connexion.php");
    exit;
}

$nomClient = $_SESSION['nom'] ?? '';
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
     <p>Bienvenue, <?php echo htmlspecialchars($nomClient); ?> !</p>
   </main>
</body>
</html>