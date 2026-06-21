<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
// À RETIRER avant la mise en ligne définitive.
// En production :
// ini_set('display_errors', 0);   // n'affiche RIEN à l'écran
// error_reporting(E_ALL);          // continue à TOUT détecter
// ini_set('log_errors', 1);        // mais écrit tout dans un fichier log
// ini_set('error_log', '/chemin/vers/erreurs.log');

include("../connexion/cnx.php");
session_start();

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    // --- Protection CSRF ---
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header("Location: inscriptionOuvrier.php");
        exit;
    }

    // --- Récupération des champs ---
    $nom              = trim($_POST["nom"]);
    $prenom           = trim($_POST["prenom"]);
    $email            = trim($_POST["email"]);
    $telephone        = trim($_POST["telephone"]);
    $motDePasse       = $_POST["mot_de_passe"];
    $confirmemotPasse = $_POST["confirmation_mot_de_passe"];
    $categorie        = trim($_POST["categorie"]);
    $description      = trim($_POST["description"]);

    // IMPORTANT : zone_intervention arrive comme un TABLEAU, car le
    // <select multiple name="zone_intervention[]"> peut renvoyer
    // plusieurs valeurs cochées en même temps. On sécurise avec
    // ?? [] au cas où aucune zone n'aurait été cochée.
    $zonesIntervention = $_POST["zone_intervention"] ?? [];

    $erreurs = [];

    if (empty($nom)) {
        $erreurs['nom'] = "Le nom est obligatoire";
    } elseif (!preg_match('/^[a-zA-ZÀ-ÿ\s\-]{2,50}$/u', $nom)) {
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

    if (empty($motDePasse)) {
        $erreurs['mot_de_passe'] = "Le mot de passe est obligatoire";
    } elseif (strlen($motDePasse) < 8) {
        $erreurs['mot_de_passe'] = "8 caractères minimum";
    } elseif (!preg_match('/[A-Z]/', $motDePasse) || !preg_match('/[0-9]/', $motDePasse)) {
        $erreurs['mot_de_passe'] = "Le mot de passe doit contenir au moins une majuscule et un chiffre";
    }

    if (empty($confirmemotPasse)) {
        $erreurs['confirmation_mot_de_passe'] = "Veuillez confirmer le mot de passe";
    } elseif ($motDePasse !== $confirmemotPasse) {
        $erreurs['confirmation_mot_de_passe'] = "Les mots de passe ne correspondent pas";
    }

    if (empty($categorie)) {
        $erreurs['categorie'] = "Veuillez sélectionner un métier";
    }

    if (empty($zonesIntervention)) {
        $erreurs['zone_intervention'] = "Sélectionnez au moins une zone d'intervention";
    }

    if (empty($description)) {
        $erreurs['description'] = "Merci de décrire votre activité";
    } elseif (strlen($description) < 20) {
        $erreurs['description'] = "Décrivez votre activité en au moins 20 caractères";
    }

    if (!isset($_POST['cgu'])) {
        $erreurs['cgu'] = "Vous devez accepter les conditions d'utilisation";
    }

    // --- Si erreurs : on renvoie tout vers le formulaire ---
    if (!empty($erreurs)) {
        $_SESSION['erreurs_inscription'] = $erreurs;
        $_SESSION['anciennes_valeurs'] = [
            'nom'               => htmlspecialchars($nom),
            'prenom'            => htmlspecialchars($prenom),
            'email'             => htmlspecialchars($email),
            'telephone'         => htmlspecialchars($telephone),
            'categorie'         => htmlspecialchars($categorie),
            'description'       => htmlspecialchars($description),
            'zone_intervention' => $zonesIntervention, // tableau, pas besoin d'échapper ici (sera fait à l'affichage)
        ];
        header("Location: inscriptionOuvrier.php");
        exit;
    }

    // --- Vérifier que l'email n'est pas déjà utilisé ---
    $verifEmail = $cnx->prepare("SELECT IdOuvrier FROM ouvrier WHERE EmailOuvrier = :email");
    $verifEmail->execute(["email" => $email]);

    if ($verifEmail->fetch()) {
        $_SESSION['erreurs_inscription'] = ['email' => "Cet email est déjà utilisé"];
        header("Location: inscriptionOuvrier.php");
        exit;
    }

    // --- Hachage du mot de passe ---
    $motDePasseHache = password_hash($motDePasse, PASSWORD_DEFAULT);

    // --- Fusion du tableau de zones en une seule chaîne ---
    // ZoneIntervention est une colonne texte unique en base, donc on
    // rassemble les communes cochées séparées par une virgule.
    // Ex: ['cocody', 'plateau'] -> "cocody, plateau"
    $zonesTexte = implode(", ", $zonesIntervention);

    // --- Insertion en base ---
    $req = $cnx->prepare(
        "INSERT INTO ouvrier (NomOuvrier, PrenomOuvrier, EmailOuvrier, TelephoneOuvrier, motDepasseOuvrier, ZoneIntervention, Categorie, Description)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
    );

    if ($req->execute([$nom, $prenom, $email, $telephone, $motDePasseHache, $zonesTexte, $categorie, $description])) {

        session_regenerate_id(true);

        $_SESSION["id_ouvrier"] = $cnx->lastInsertId();
        $_SESSION["nom"] = $nom;
        $_SESSION["type_utilisateur"] = "ouvrier";

        unset($_SESSION['csrf_token']);

        header("Location: ../Ouvrier/dashboard.php");
        exit;
    } else {
        $_SESSION['erreurs_inscription'] = ['global' => "Une erreur est survenue, veuillez réessayer"];
        header("Location: inscription_ouvrier.php");
        exit;
    }

} else {
    header("Location: inscriptionOuvrier.php");
    exit;
}