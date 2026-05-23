<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

$logged = isset($_SESSION['stato']) && $_SESSION['stato'] === 1;
$userName  = $_SESSION['user_name']  ?? 'user';
$userEmail = $_SESSION['user_email'] ?? '';
$role      = $_SESSION['role']       ?? 'utenete';
$User_name = $_SESSION['UserName']  ?? 'Tu';


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? null;
    $id= $pdo->prepare("SELECT id FROM utenti WHERE Email=:email");
    $id->execute([":email"=>$userEmail]);
    $id_utente= $id->fetchColumn();
    if ($action === "public") {
        querypubblicazione($pdo,$_POST["titolo"],$_POST["sottotitolo"],$_POST["contenuto"],$id_utente);
    }
    header("Location: " . "home.php");
    exit;
}

function querypubblicazione(PDO $pdo, string $titolo,string $sottotitolo,string $contenuto,int $utente){
$pubblica=$pdo->prepare("INSERT INTO articoli
                        (titolo, sottotitolo, contenuto, id_utente)
                        VALUES (:titolo, :sottotitolo, :contenuto, :utente)");
$pubblica->execute([":titolo"=> $titolo,":sottotitolo"=> $sottotitolo,":contenuto"=> $contenuto,":utente"=> $utente]);
}
?>

<!doctype html>
<html lang="it">
<head>
    <?php require_once __DIR__ . '/head.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portale dello scrittore</title>
    <link rel="stylesheet" href="assets/style.css?v=3">
</head>
<body>
  <main class="page">
    <section class="app-card">
        <header class="app-header">
            <a class="linkrow" href="./"><div class="mark" aria-hidden="true"></div></a>
            <div class="app-header-text">
                <h1>Portale dello scrittore</h1>
                <p class="subtitle">Scrivi e leggi nuove notizie ogni giorno</p>
            </div>
            <?php if ($logged): ?>
                <!-- <div class="user-badge" style="display:flex;align-items:center;gap:10px;padding:8px 10px;border:1px solid var(--line);border-radius:14px;background:rgba(255,255,255,.70);">
                        <div class="avatar" style="width:38px;height:38px;border-radius:12px;background:linear-gradient(135deg,var(--accent),#60a5fa);display:grid;place-items:center;color:#fff;font-weight:700;font-size:15px;">-->
                <div class="user-badge">
                    <div class="avatar" >
                        <?= strtoupper(substr($userName, 0, 1)) ?>
                    </div>
                    <div class="user-meta">
                        <div class="user-name"><?php echo htmlspecialchars($userName); ?> · <?php echo htmlspecialchars($User_name); ?></div>
                        <div class="user-sub"><?php echo htmlspecialchars($userEmail); ?> · <?php echo htmlspecialchars($role); ?></div>
                    </div>
                </div>
            <?php endif; ?>
        </header>

        <div class="app-body">
            <!-- Bottone indietro -->
            <a href="home.php" style="display:inline-flex;align-items:center;gap:6px;font-size:13px;color:var(--accent);text-decoration:none;margin-bottom:24px;">
                ← Torna alla home
            </a>
                <div class="container">

    <h2>Scrivi un articolo giornalistico</h2>

    

    <form method="POST">

        <input type="text" name="titolo" placeholder="Titolo articolo">

        <input type="text" name="sottotitolo" placeholder="sototitolo Titolo articolo">

        <textarea name="contenuto" rows="10" placeholder="Scrivi l'articolo..."></textarea>

        <input type="hidden" name="action" value="public" >
        <button type="submit">Pubblica</button>

    </form>

</div>
        </div>

      <footer class="app-footer">
        <span>© <?php echo date('Y'); ?> – News Progect</span>
      </footer>

    </section>
  </main>
</body>