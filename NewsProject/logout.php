<?php
// logout.php - chiude la sessione e torna al login
session_start();

// Svuota tutte le variabili di sessione
$_SESSION = [];

// Se esiste il cookie di sessione, eliminalo
if (ini_get("session.use_cookies")) {
  $params = session_get_cookie_params();
  setcookie(
    session_name(),
    '',
    time() - 42000,
    $params['path'],
    $params['domain'],
    $params['secure'],
    $params['httponly']
  );
}

// Distruggi la sessione
session_destroy();

// Torna alla pagina di login
header("Location: login.php");
exit;