<!-- traitement  du formlaire d'inscrition client -->
<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
// À RETIRER avant la mise en ligne définitive (ne jamais exposer les
// erreurs PHP à un visiteur en production).

include("../connexion/cnx.php");
session_start();

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    // SÉCURITÉ 1 — Protection CSRF (Cross-Site Request Forgery)
    // Un formulaire sans protection CSRF peut être soumis depuis un
    // AUTRE site web à ton insu (un pirate piège un utilisateur connecté
    // sur un site malveillant qui renvoie discrètement une requête vers
    // ton serveur). Le jeton CSRF est une valeur secrète générée par PHP,
    // stockée en session ET injectée dans un champ caché du formulaire.
    // Si les deux ne correspondent pas exactement, on rejette la requête :
    // ça prouve que le formulaire ne vient pas vraiment de chez toi.
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header("Location: inscriptionClient.php?erreur_globale=" . urlencode("Requête invalide, veuillez réessayer"));
        exit;
    }

    // --- Récupération des champs ---
    $nom              = trim($_POST["nom"]);
    $prenom           = trim($_POST["prenom"]);
    $email            = trim($_POST["email"]);
    $telephone        = trim($_POST["telephone"]);
    $commune          = trim($_POST["commune"]);
    $motDePasse       = $_POST["motDepasse"];               // jamais de trim() sur un mot de passe
    $confirmemotPasse = $_POST["confirmationmotdepasse"];

    // VALIDATION — un tableau d'erreurs PAR CHAMP, pas un message global
    // Chaque clé du tableau correspond à un name="" du formulaire HTML.
    // S'il n'y a pas d'erreur pour un champ, sa clé n'existe simplement
    // pas dans le tableau.
    $erreurs = [];

    if (empty($nom)) {
        $erreurs['nom'] = "Le nom est obligatoire";
    } elseif (!preg_match('/^[a-zA-ZÀ-ÿ\s\-]{2,50}$/u', $nom)) {
        // SÉCURITÉ 2 — Validation stricte par expression régulière.
        // On n'accepte que des lettres (y compris accentuées), espaces
        // et tirets, entre 2 et 50 caractères. Ça bloque par exemple
        // une tentative d'injection de balises <script> dans ce champ.
        $erreurs['nom'] = "Le nom ne doit contenir que des lettres";
    }

    if (empty($prenom)) {
        $erreurs['prenom'] = "Le prénom est obligatoire";
    } elseif (!preg_match('/^[a-zA-ZÀ-ÿ\s\-]{2,50}$/u', $prenom)) {
        $erreurs['prenom'] = "Le prénom ne doit contenir que des lettres";
    }

    if (empty($email)) {
        $erreurs['email'] = "L'email est obligatoire";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreurs['email'] = "Format d'email invalide";
    }

    if (empty($telephone)) {
        $erreurs['telephone'] = "Le téléphone est obligatoire";
    } elseif (!preg_match('/^[0-9+\s]{8,20}$/', $telephone)) {
        $erreurs['telephone'] = "Format de téléphone invalide";
    }

    if (empty($commune)) {
        $erreurs['commune'] = "Veuillez sélectionner une commune";
    }

    if (empty($motDePasse)) {
        $erreurs['motDepasse'] = "Le mot de passe est obligatoire";
    } elseif (strlen($motDePasse) < 8) {
        $erreurs['motDepasse'] = "8 caractères minimum";
    } elseif (!preg_match('/[A-Z]/', $motDePasse) || !preg_match('/[0-9]/', $motDePasse)) {
        // SÉCURITÉ 3 — Exiger un minimum de complexité (au moins une
        // majuscule et un chiffre) réduit fortement le risque de mots
        // de passe trop simples comme "azertyui".
        $erreurs['motDepasse'] = "Le mot de passe doit contenir au moins une majuscule et un chiffre";
    }

    if (empty($confirmemotPasse)) {
        $erreurs['confirmationmotdepasse'] = "Veuillez confirmer le mot de passe";
    } elseif ($motDePasse !== $confirmemotPasse) {
        $erreurs['confirmationmotdepasse'] = "Les mots de passe ne correspondent pas";
    }

    if (!isset($_POST['cgu'])) {
        $erreurs['cgu'] = "Vous devez accepter les conditions d'utilisation";
    }

    // Si des erreurs existent : on retourne tout vers le formulaire
    if (!empty($erreurs)) {
        // On encode le tableau d'erreurs en JSON pour le faire voyager
        // dans l'URL. C'est correct ici car il n'y a rien de sensible
        // dedans (juste des messages d'erreur publics).
        $_SESSION['erreurs_inscription'] = $erreurs;

        // On sauvegarde aussi ce que l'utilisateur avait tapé (SAUF les
        // mots de passe, qu'on ne renvoie jamais) pour pré-remplir le
        // formulaire et lui éviter de tout retaper.
        $_SESSION['anciennes_valeurs'] = [
            'nom'       => htmlspecialchars($nom),
            'prenom'    => htmlspecialchars($prenom),
            'email'     => htmlspecialchars($email),
            'telephone' => htmlspecialchars($telephone),
            'commune'   => htmlspecialchars($commune),
        ];

        header("Location: inscriptionClient.php");
        exit;
    }

    // Vérifier que l'email n'est pas déjà utilisé
    $verifEmail = $cnx->prepare("SELECT IdClient FROM client WHERE EmailClient = :email");
    $verifEmail->execute(["email" => $email]);

    if ($verifEmail->fetch()) {
        $_SESSION['erreurs_inscription'] = ['email' => "Cet email est déjà utilisé"];
        $_SESSION['anciennes_valeurs'] = [
            'nom' => htmlspecialchars($nom), 'prenom' => htmlspecialchars($prenom),
            'telephone' => htmlspecialchars($telephone), 'commune' => htmlspecialchars($commune),
        ];
        header("Location: inscriptionClient.php");
        exit;
    }


    // SÉCURITÉ 4 — Hachage du mot de passe (bcrypt via password_hash)
    $motDePasseHache = password_hash($motDePasse, PASSWORD_DEFAULT);

    // Insertion en base (requête préparée = protection anti-injection SQL)
    $req = $cnx->prepare(
        "INSERT INTO client (NomClient, PrenomClient, EmailClient, TelephoneClient, motDepasseClient, CommuneResidence)
         VALUES (?, ?, ?, ?, ?, ?)"
    );

    if ($req->execute([$nom, $prenom, $email, $telephone, $motDePasseHache, $commune])) {

        // SÉCURITÉ 5 — Régénération de l'identifiant de session.
        // Empêche une attaque de "fixation de session" : on force PHP
        // à générer un NOUVEL identifiant de session au moment où
        // l'utilisateur change de statut (ici : il devient connecté).
        session_regenerate_id(true);

        $_SESSION["id_client"] = $cnx->lastInsertId();
        $_SESSION["nom"] = $nom;
        $_SESSION["type_utilisateur"] = "client";

        // On retire le jeton CSRF utilisé : un jeton ne doit servir qu'une fois.
        unset($_SESSION['csrf_token']);

        header("Location: ../Client/dashboard.php");
        exit;
    } else {
        $_SESSION['erreurs_inscription'] = ['global' => "Une erreur est survenue, veuillez réessayer"];
        header("Location: inscriptionClient.php");
        exit;
    }

} else {
    header("Location: inscriptionClient.php");
    exit;
}