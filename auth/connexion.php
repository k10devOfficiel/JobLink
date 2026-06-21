<?php
session_start();

// On génère un nouveau jeton CSRF à chaque chargement du formulaire
// (sauf s'il en existe déjà un en attente, même logique que sur les
// pages d'inscription).
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Récupération des erreurs et de l'ancienne valeur envoyées par
// traitement_connexion.php, puis on les efface de la session pour
// qu'elles ne réapparaissent pas à un simple rechargement de page.
$erreurs   = $_SESSION['erreurs_connexion'] ?? [];
$anciennes = $_SESSION['anciennes_valeurs'] ?? [];
unset($_SESSION['erreurs_connexion'], $_SESSION['anciennes_valeurs']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Connexion</title>
<link rel="stylesheet" href="../css/auth.css">
</head>
<body>

  <?php include("../include/include.html"); ?>

  <div class="page-connexion">

    <!-- Carte centrale de connexion -->
    <main class="conteneur-connexion">
      <div class="carte-connexion">

        <h1>Connexion</h1>
        <p class="sous-titre">Accédez à votre espace JobLink </p>

        <?php if (isset($erreurs['global'])): ?>
          <div class="message-contexte message-erreur-globale">
            <?php echo htmlspecialchars($erreurs['global']); ?>
          </div>
        <?php else: ?>
          <div class="message-contexte">
            Connectez-vous pour contacter cet ouvrier
          </div>
        <?php endif; ?>

        <form action="traitementConnexion.php" method="POST" class="formulaire">

          <!-- Jeton CSRF, vérifié par traitement_connexion.php -->
          <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

          <div class="groupe-champ">
            <label for="identifiant">Email ou téléphone</label>
            <input
              type="text"
              id="identifiant"
              name="identifiant"
              placeholder="exemple@email.com"
              value="<?php echo isset($anciennes['identifiant']) ? htmlspecialchars($anciennes['identifiant']) : ''; ?>"
              class="<?php echo isset($erreurs['identifiant']) ? 'champ-erreur' : ''; ?>"
              required
            >
            <?php if (isset($erreurs['identifiant'])): ?>
              <span class="message-erreur-champ"><?php echo htmlspecialchars($erreurs['identifiant']); ?></span>
            <?php endif; ?>
          </div>

          <div class="groupe-champ">
            <label for="mot_de_passe">Mot de passe</label>
            <input
              type="password"
              id="mot_de_passe"
              name="mot_de_passe"
              placeholder="••••••••"
              class="<?php echo isset($erreurs['mot_de_passe']) ? 'champ-erreur' : ''; ?>"
              required
            >
            <?php if (isset($erreurs['mot_de_passe'])): ?>
              <span class="message-erreur-champ"><?php echo htmlspecialchars($erreurs['mot_de_passe']); ?></span>
            <?php endif; ?>
          </div>

          <a href="mot_de_passe_oublie.php" class="lien-mot-de-passe-oublie">
            Mot de passe oublié ?
          </a>

          <button type="submit" class="bouton-principal">
            Se connecter
          </button>

        </form>

        <div class="separateur">
          <span>ou</span>
        </div>

        <div class="liens-secondaires">
          <a href="inscriptionClient.php" class="bouton-secondaire">
            Pas de compte ? Créer un compte client
          </a>
          <a href="inscriptionOuvrier.php" class="bouton-secondaire">
            Devenir ouvrier créer un profil Ouvrier
          </a>
        </div>

      </div>
    </main>

  </div>

</body>
</html>