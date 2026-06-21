<?php
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$erreurs = $_SESSION['erreurs_inscription'] ?? [];
$anciennes = $_SESSION['anciennes_valeurs'] ?? [];
unset($_SESSION['erreurs_inscription'], $_SESSION['anciennes_valeurs']);

function afficherErreur($erreurs, $champ) {
    if (isset($erreurs[$champ])) {
        echo '<span class="message-erreur-champ">' . htmlspecialchars($erreurs[$champ]) . '</span>';
    }
}

function classeErreur($erreurs, $champ) {
    return isset($erreurs[$champ]) ? 'champ-erreur' : '';
}

function ancienneValeur($anciennes, $champ) {
    return isset($anciennes[$champ]) ? htmlspecialchars($anciennes[$champ]) : '';
}

// Le multi-select des zones renvoie un TABLEAU, pas une simple chaîne :
// fonction dédiée pour savoir si une commune précise doit rester cochée.
function communeSelectionnee($anciennes, $valeur) {
    return (isset($anciennes['zone_intervention']) && in_array($valeur, $anciennes['zone_intervention'])) ? 'selected' : '';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Devenir ouvrier — JobLink CI</title>
<link rel="stylesheet" href="../css/auth.css">
</head>
<body>

  <?php include("../include/include.html"); ?>

  <div class="page-connexion">

    <main class="conteneur-connexion">
      <div class="carte-connexion carte-large">

        <h1>Devenir ouvrier sur JobLink</h1>
        <p class="sous-titre">Créez votre profil professionnel et recevez des demandes de clients près de chez vous</p>

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

        <form action="../traitement/inscriptionOuvrier.php" method="POST" class="formulaire" novalidate>

          <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

          <p class="titre-section">Informations personnelles</p>

          <div class="ligne-champs">
            <div class="groupe-champ">
              <label for="nom">Nom</label>
              <input
                type="text"
                id="nom"
                name="nom"
                placeholder="Koffi"
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
                placeholder="Yao"
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
              name="mot_de_passe"
              placeholder="8 caractères, 1 majuscule, 1 chiffre minimum"
              minlength="8"
              class="<?php echo classeErreur($erreurs, 'mot_de_passe'); ?>"
              required
            >
            <?php afficherErreur($erreurs, 'mot_de_passe'); ?>
          </div>

          <div class="groupe-champ">
            <label for="confirmation_mot_de_passe">Confirmer le mot de passe</label>
            <input
              type="password"
              id="confirmation_mot_de_passe"
              name="confirmation_mot_de_passe"
              placeholder="8 caractères minimum"
              minlength="8"
              class="<?php echo classeErreur($erreurs, 'confirmation_mot_de_passe'); ?>"
              required
            >
            <?php afficherErreur($erreurs, 'confirmation_mot_de_passe'); ?>
          </div>

          <p class="titre-section">Profil professionnel</p>

          <div class="groupe-champ">
            <label for="categorie">Métier / Catégorie</label>
            <select
              id="categorie"
              name="categorie"
              class="<?php echo classeErreur($erreurs, 'categorie'); ?>"
              required
            >
              <option value="" disabled <?php echo empty($anciennes['categorie']) ? 'selected' : ''; ?>>Sélectionnez votre métier</option>
              <option value="plomberie" <?php echo (($anciennes['categorie'] ?? '') === 'plomberie') ? 'selected' : ''; ?>>Plomberie</option>
              <option value="electricite" <?php echo (($anciennes['categorie'] ?? '') === 'electricite') ? 'selected' : ''; ?>>Électricité</option>
              <option value="maconnerie" <?php echo (($anciennes['categorie'] ?? '') === 'maconnerie') ? 'selected' : ''; ?>>Maçonnerie</option>
              <option value="menuiserie" <?php echo (($anciennes['categorie'] ?? '') === 'menuiserie') ? 'selected' : ''; ?>>Menuiserie</option>
              <option value="peinture" <?php echo (($anciennes['categorie'] ?? '') === 'peinture') ? 'selected' : ''; ?>>Peinture</option>
              <option value="climatisation" <?php echo (($anciennes['categorie'] ?? '') === 'climatisation') ? 'selected' : ''; ?>>Climatisation</option>
            </select>
            <?php afficherErreur($erreurs, 'categorie'); ?>
          </div>

          <div class="groupe-champ">
            <label for="zone_intervention">Zone(s) d'intervention</label>
            <select
              id="zone_intervention"
              name="zone_intervention[]"
              class="<?php echo classeErreur($erreurs, 'zone_intervention'); ?>"
              multiple
              required
            >
              <option value="abobo" <?php echo communeSelectionnee($anciennes, 'abobo'); ?>>Abobo</option>
              <option value="adjame" <?php echo communeSelectionnee($anciennes, 'adjame'); ?>>Adjamé</option>
              <option value="attecoube" <?php echo communeSelectionnee($anciennes, 'attecoube'); ?>>Attécoubé</option>
              <option value="cocody" <?php echo communeSelectionnee($anciennes, 'cocody'); ?>>Cocody</option>
              <option value="koumassi" <?php echo communeSelectionnee($anciennes, 'koumassi'); ?>>Koumassi</option>
              <option value="marcory" <?php echo communeSelectionnee($anciennes, 'marcory'); ?>>Marcory</option>
              <option value="plateau" <?php echo communeSelectionnee($anciennes, 'plateau'); ?>>Plateau</option>
              <option value="port-bouet" <?php echo communeSelectionnee($anciennes, 'port-bouet'); ?>>Port-Bouët</option>
              <option value="treichville" <?php echo communeSelectionnee($anciennes, 'treichville'); ?>>Treichville</option>
              <option value="yopougon" <?php echo communeSelectionnee($anciennes, 'yopougon'); ?>>Yopougon</option>
            </select>
            <span class="aide-champ">Maintenez Ctrl (ou Cmd sur Mac) pour sélectionner plusieurs communes</span>
            <?php afficherErreur($erreurs, 'zone_intervention'); ?>
          </div>

          <div class="groupe-champ">
            <label for="description">Description de votre activité</label>
            <textarea
              id="description"
              name="description"
              rows="4"
              placeholder="Présentez votre expérience, vos spécialités, vos disponibilités..."
              class="<?php echo classeErreur($erreurs, 'description'); ?>"
              required
            ><?php echo ancienneValeur($anciennes, 'description'); ?></textarea>
            <?php afficherErreur($erreurs, 'description'); ?>
          </div>

          <div class="groupe-case-a-cocher">
            <input type="checkbox" id="cgu" name="cgu" required>
            <label for="cgu">J'accepte les conditions d'utilisation</label>
          </div>
          <?php afficherErreur($erreurs, 'cgu'); ?>

          <button type="submit" class="bouton-principal">
            Créer mon profil ouvrier
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