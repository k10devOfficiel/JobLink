<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
// À RETIRER avant la mise en ligne définitive.

include("../connexion/cnx.php");
session_start();

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    // --- Protection CSRF (même logique que les inscriptions) ---
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header("Location:connexion.php");
        exit;
    }

    $identifiant = trim($_POST["identifiant"]);
    $motDePasse  = $_POST["mot_de_passe"]; // jamais de trim() sur un mot de passe

    $erreurs = [];

    if (empty($identifiant)) {
        $erreurs['identifiant'] = "Email ou téléphone obligatoire";
    }
    if (empty($motDePasse)) {
        $erreurs['mot_de_passe'] = "Mot de passe obligatoire";
    }

    if (!empty($erreurs)) {
        $_SESSION['erreurs_connexion'] = $erreurs;
        $_SESSION['anciennes_valeurs'] = ['identifiant' => htmlspecialchars($identifiant)];
        header("Location:connexion.php");
        exit;
    }

    // ------------------------------------------------------------------
    // PRINCIPE : on ne sait pas, à ce stade, si la personne qui se
    // connecte est un client ou un ouvrier (le formulaire est unique).
    // On cherche donc d'abord dans la table client (par email OU par
    // téléphone), et si rien ne correspond, on cherche dans la table
    // ouvrier. Le compte trouvé EN PREMIER avec un mot de passe valide
    // détermine le type d'utilisateur et donc la page de redirection.
    // ------------------------------------------------------------------

    // --- 1) Recherche côté client ---
    $reqClient = $cnx->prepare(
        "SELECT IdClient, NomClient, EmailClient, motDepasseClient
         FROM client
         WHERE EmailClient = :id OR TelephoneClient = :id"
    );
    $reqClient->execute(["id" => $identifiant]);
    $client = $reqClient->fetch();

    if ($client && password_verify($motDePasse, $client['motDepasseClient'])) {

        // SÉCURITÉ — Régénération de l'identifiant de session pour
        // empêcher une fixation de session au moment de la connexion.
        session_regenerate_id(true);

        $_SESSION["id_client"]        = $client['IdClient'];
        $_SESSION["nom"]              = $client['NomClient'];
        $_SESSION["type_utilisateur"] = "client";

        unset($_SESSION['csrf_token']);

        header("Location: ../Client/dashboard.php");
        exit;
    }

    // --- 2) Recherche côté ouvrier (uniquement si pas trouvé en client) 
    $reqOuvrier = $cnx->prepare(
        "SELECT IdOuvrier, NomOuvrier, EmailOuvrier, motDepasseOuvrier
         FROM ouvrier
         WHERE EmailOuvrier = :id OR TelephoneOuvrier = :id"
    );
    $reqOuvrier->execute(["id" => $identifiant]);
    $ouvrier = $reqOuvrier->fetch();

    if ($ouvrier && password_verify($motDePasse, $ouvrier['motDepasseOuvrier'])) {

        session_regenerate_id(true);

        $_SESSION["id_ouvrier"]       = $ouvrier['IdOuvrier'];
        $_SESSION["nom"]              = $ouvrier['NomOuvrier'];
        $_SESSION["type_utilisateur"] = "ouvrier";

        unset($_SESSION['csrf_token']);

        header("Location: ../Ouvrier/dashboard.php");
        exit;
    }

    // --- 3) Ni client, ni ouvrier, ou mot de passe incorrect ---
    // SÉCURITÉ — Message volontairement vague : on ne précise jamais
    // si c'est l'identifiant ou le mot de passe qui est faux, pour ne
    // pas révéler à un attaquant qu'un email/téléphone existe en base.
    $_SESSION['erreurs_connexion'] = ['global' => "Identifiant ou mot de passe incorrect"];
    $_SESSION['anciennes_valeurs'] = ['identifiant' => htmlspecialchars($identifiant)];
    header("Location:connexion.php");
    exit;

} else {
    header("Location:connexion.php");
    exit;
}