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

        <div class="message-contexte">
          Connectez-vous pour contacter cet ouvrier
        </div>

        <form action="traitement_connexion.php" method="POST" class="formulaire">

          <div class="groupe-champ">
            <label for="identifiant">Email ou téléphone</label>
            <input
              type="text"
              id="identifiant"
              name="identifiant"
              placeholder="exemple@email.com"
              required
            >
          </div>

          <div class="groupe-champ">
            <label for="mot_de_passe">Mot de passe</label>
            <input
              type="password"
              id="mot_de_passe"
              name="mot_de_passe"
              placeholder="••••••••"
              required
            >
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
          <a href="../auth/inscriptionClient.php" class="bouton-secondaire">
            Pas de compte ? Créer un compte client
          </a>
          <a href="../auth/inscriptionOuvrier.php" class="bouton-secondaire">
            Devenir ouvrier créer un profil Ouvrie
          </a>
        </div>

      </div>
    </main>

  

  </div>

</body>
</html>