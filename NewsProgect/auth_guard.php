<?php
// auth_guard.php - Protegge le pagine private (serve sessione valida)

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Evita che pagine private restino in cache (utile dopo logout)
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

if (empty($_SESSION['user_email'])) {
  header("Location: login.php");
  exit;
}