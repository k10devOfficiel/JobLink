<?php
session_start();

// On génère un nouveau jeton CSRF à chaque chargement du formulaire
// (sauf s'il en existe déjà un en attente, pour éviter d'en recréer un
// à chaque rechargement après erreur).
if (empty($_SESSION['csrf_token'])) {
    // bin2hex(random_bytes(32)) génère une chaîne aléatoire longue et
    // imprévisible : impossible à deviner pour un attaquant.
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Récupération des erreurs et anciennes valeurs envoyées par
// traitementConnexion.php, puis on les efface de la session pour
// qu'elles ne réapparaissent pas si l'utilisateur recharge la page
// plus tard sans avoir resoumis le formulaire.
$erreurs = $_SESSION['erreurs_inscription'] ?? [];
$anciennes = $_SESSION['anciennes_valeurs'] ?? [];
unset($_SESSION['erreurs_inscription'], $_SESSION['anciennes_valeurs']);

/**
 * Petite fonction d'aide : affiche le message d'erreur d'un champ s'il
 * existe, sinon n'affiche rien. Centralise l'affichage pour ne pas
 * répéter la même logique HTML sous chaque champ.
 */
function afficherErreur($erreurs, $champ) {
    if (isset($erreurs[$champ])) {
        echo '<span class="message-erreur-champ">' . htmlspecialchars($erreurs[$champ]) . '</span>';
    }
}

/**
 * Ajoute la classe CSS "champ-erreur" si ce champ précis est en erreur,
 * pour le surligner visuellement (bordure rouge, voir auth.css).
 */
function classeErreur($erreurs, $champ) {
    return isset($erreurs[$champ]) ? 'champ-erreur' : '';
}

/**
 * Récupère l'ancienne valeur saisie par l'utilisateur pour un champ,
 * pour ne pas le faire tout retaper après une erreur.
 */
function ancienneValeur($anciennes, $champ) {
    return isset($anciennes[$champ]) ? htmlspecialchars($anciennes[$champ]) : '';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Créer un compte client JobLink</title>
<link rel="stylesheet" href="../css/auth.css">
</head>
<body>

  <?php include("../include/include.html"); ?>

  <div class="page-connexion">

    <main class="conteneur-connexion">
      <div class="carte-connexion">

        <h1>Créer un compte client</h1>
        <p class="sous-titre">Rejoignez JobLink pour contacter des ouvriers qualifiés</p>

        <?php if (isset($erreurs['global'])): ?>
          <div class="message-contexte message-erreur-globale">
            <?php echo htmlspecialchars($erreurs['global']); ?>
          </div>
        <?php endif; ?>
        <?php if (isset($erreurs['email']) && $erreurs['email'] === "Cet email est déjà utilisé"): ?>
          <div class="message-contexte message-erreur-globale">
            Cet email est déjà utilisé — <a href="connexion.php">se connecter ?</a>
          </div>
        <?php endif; ?>

        <form action="../traitement/inscriptionClient.php" method="POST" class="formulaire" novalidate>

          <!-- Jeton CSRF : champ invisible pour l'utilisateur, vérifié
               par traitementConnexion.php avant tout traitement. -->
          <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

          <div class="ligne-champs">
            <div class="groupe-champ">
              <label for="nom">Nom</label>
              <input
                type="text"
                id="nom"
                name="nom"
                placeholder="Kouassi"
                value="<?php echo ancienneValeur($anciennes, 'nom'); ?>"
                class="<?php echo classeErreur($erreurs, 'nom'); ?>"
                required
              >
              <?php afficherErreur($erreurs, 'nom'); ?>
            </div>

            <div class="groupe-champ">
              <label for="prenom">Prénom</label>
              <input
                type="text"
                id="prenom"
                name="prenom"
                placeholder="Aya"
                value="<?php echo ancienneValeur($anciennes, 'prenom'); ?>"
                class="<?php echo classeErreur($erreurs, 'prenom'); ?>"
                required
              >
              <?php afficherErreur($erreurs, 'prenom'); ?>
            </div>
          </div>

          <div class="groupe-champ">
            <label for="email">Email</label>
            <input
              type="email"
              id="email"
              name="email"
              placeholder="exemple@email.com"
              value="<?php echo ancienneValeur($anciennes, 'email'); ?>"
              class="<?php echo classeErreur($erreurs, 'email'); ?>"
              required
            >
            <?php afficherErreur($erreurs, 'email'); ?>
          </div>

          <div class="groupe-champ">
            <label for="telephone">Téléphone (de préférence WhatsApp)</label>
            <input
              type="tel"
              id="telephone"
              name="telephone"
              placeholder="+225 07 00 00 00 00"
              value="<?php echo ancienneValeur($anciennes, 'telephone'); ?>"
              class="<?php echo classeErreur($erreurs, 'telephone'); ?>"
              required
            >
            <?php afficherErreur($erreurs, 'telephone'); ?>
          </div>

          <div class="groupe-champ">
            <label for="mot_de_passe">Mot de passe</label>
            <input
              type="password"
              id="mot_de_passe"
              name="motDepasse"
              placeholder="8 caractères, 1 majuscule, 1 chiffre minimum"
              minlength="8"
              class="<?php echo classeErreur($erreurs, 'motDepasse'); ?>"
              required
            >
            <?php afficherErreur($erreurs, 'motDepasse'); ?>
          </div>

          <div class="groupe-champ">
            <label for="confirmation_mot_de_passe">Confirmer le mot de passe</label>
            <input
              type="password"
              id="confirmation_mot_de_passe"
              name="confirmationmotdepasse"
              placeholder="8 caractères minimum"
              minlength="8"
              class="<?php echo classeErreur($erreurs, 'confirmationmotdepasse'); ?>"
              required
            >
            <?php afficherErreur($erreurs, 'confirmationmotdepasse'); ?>
          </div>

          <div class="groupe-champ">
            <label for="commune">Commune de résidence</label>
            <select
              id="commune"
              name="commune"
              class="<?php echo classeErreur($erreurs, 'commune'); ?>"
              required
            >
              <option value="" disabled <?php echo empty($anciennes['commune']) ? 'selected' : ''; ?>>Sélectionnez votre commune</option>
              <?php
              $communes = ['abobo' => 'Abobo', 'adjame' => 'Adjamé', 'attecoube' => 'Attécoubé', 'cocody' => 'Cocody', 'koumassi' => 'Koumassi', 'marcory' => 'Marcory', 'plateau' => 'Plateau', 'port-bouet' => 'Port-Bouët', 'treichville' => 'Treichville', 'yopougon' => 'Yopougon'];
              foreach ($communes as $valeur => $libelle) {
                  $selectionne = (isset($anciennes['commune']) && $anciennes['commune'] === $valeur) ? 'selected' : '';
                  echo "<option value=\"$valeur\" $selectionne>$libelle</option>";
              }
              ?>
            </select>
            <?php afficherErreur($erreurs, 'commune'); ?>
          </div>

          <div class="groupe-case-a-cocher">
            <input type="checkbox" id="cgu" name="cgu" required>
            <label for="cgu">J'accepte les conditions d'utilisation</label>
          </div>
          <?php afficherErreur($erreurs, 'cgu'); ?>

          <button type="submit" class="bouton-principal">
            Créer mon compte
          </button>

        </form>

        <div class="separateur">
          <span>ou</span>
        </div>

        <div class="liens-secondaires">
          <a href="connexion.php" class="bouton-secondaire">
            Déjà un compte ? Se connecter
          </a>
        </div>

      </div>
    </main>

  </div>

</body>
</html>