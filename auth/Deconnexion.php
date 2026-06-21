<?php
session_start();

// On vide toutes les variables de session...
$_SESSION = [];

// ...on supprime le cookie de session côté navigateur...
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// ...puis on détruit la session côté serveur.
session_destroy();

header("Location: connexion.php");
exit;