<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

$logged = isset($_SESSION['user_email']);
$userName  = $_SESSION['user_name']  ?? 'user';
$userEmail = $_SESSION['user_email'] ?? '';
$role      = $_SESSION['role']       ?? '';
$User_name = $_SESSION['UserName']   ?? 'Tu';

function queryelimina(PDO $pdo, int $ID){
    $delete="DELETE FROM articoli WHERE Id_articolo = :id";
    $stmt = $pdo->prepare($delete);
    $stmt->execute([":id"=> $ID]);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? null;
    $ID = isset($_POST["id"]) ? (int)$_POST["id"] : -1;
    if ($action === "delete" && $ID>-1) {
        queryelimina($pdo,$ID);
    }elseif ($action === "modifica" && $ID>-1) { 
        $modify="UPDATE articoli a SET titolo = :titolo, sottotitolo = :sottotitolo, contenuto = :contenuto, a.modify = 0 WHERE Id_articolo = :id";
        $stmt = $pdo->prepare($modify);
        $stmt->execute([":titolo"=> $_POST["titolo"],":sottotitolo"=> $_POST["sottotitolo"],":contenuto"=> $_POST["contenuto"],":id"=> $ID]);
    }
    header("location: miei_articoli.php");
    exit;
}
$articoli ="SELECT  Id_articolo,titolo,sottotitolo,DataRegistrazione,contenuto,a.modify
            FROM articoli a
            JOIN utenti u ON a.id_utente = u.id
            WHERE u.Email = :email
            ORDER BY a.DataRegistrazione";
$stmt = $pdo->prepare($articoli);
$stmt->execute([":email"=> $userEmail]);
$stmt = $stmt->fetchAll();

// Raggruppa per data
$byData = [];
foreach ($stmt  as $row) {
    $byData[$row['DataRegistrazione']][] = $row;
}



?>
<!doctype html>
<html lang="it">
<head>
    <?php require_once __DIR__ . '/head.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portale dello scrittore - i tuoi articoli</title>
    <link rel="stylesheet" href="assets/style.css?v=<?php echo filemtime('assets/style.css'); ?>">
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
                <div class="user-badge" onclick="toggleLogout()" style="cursor: pointer; position: relative;">
                    <div class="avatar">
                        <?= strtoupper(substr($userName, 0, 1)) ?>
                    </div>
                    <div class="user-meta">
                        <div class="user-name"><?php echo htmlspecialchars($userName); ?> · <?php echo htmlspecialchars($User_name); ?></div>
                        <div class="user-sub"><?php echo htmlspecialchars($userEmail); ?> · <?php echo htmlspecialchars($role); ?></div>
                    </div>
                    <div id="logout-menu" style="display: none; position: absolute; top: 100%; left: 0; background: white; border: 1px solid #ccc; padding: 10px; z-index: 100; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                        <div>
                            <a class="btn primary" href="articolo.php">+</a>
                            <a class="btn" href="logout.php">Logout</a>
                            <a class="btn" href="cambia_pw.php">new PW</a>
                
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </header>

        <div class="app-body">
            <!-- Bottone indietro -->
            <a href="home.php" style="display:inline-flex;align-items:center;gap:6px;font-size:13px;color:var(--accent);text-decoration:none;margin-bottom:24px;">
                ← Torna alla home
            </a>
            <?php if (empty($stmt)): ?>
    <div class="empty-state">
        <div class="empty-icon"></div>
        <h3>Nessun articolo</h3>
        <p>Non hai ancora pubblicato nessun articolo.</p>
    </div>

<?php else: ?>
    <div style="display:flex; align-items:center; gap:10px; margin-bottom:18px; flex-wrap:wrap;">
        <span style="font-size:15px; font-weight:700;">I tuoi articoli</span>
        <span class="pill accent"><?= count($stmt) ?> totali</span>
    </div>

    <div id="lista-articoli">
        <?php foreach ($byData as $data => $rows): ?>

            <div class="date-group">
                <div class="date-label" style="font-weight:700; margin: 16px 0 8px;">
                    <?= htmlspecialchars($data) ?>
                </div>

                <?php foreach ($rows as $row): ?>
                    <div class="notice" style="border:1px solid #ccc; padding:10px; margin:10px 0; border-radius:8px;">
                        <?php if($row['modify'] === 1): ?> <p><strong style="color:red">da modificare</strong></p> <?php endif; ?>
                        <p><strong>Titolo:</strong> <?= htmlspecialchars($row['titolo']) ?></p>
                        <p><strong>Sottotitolo:</strong> <?= htmlspecialchars($row['sottotitolo']) ?></p>
                        <p style="font-size:12px; color:#888;">
                            Pubblicato il <?= htmlspecialchars($row['DataRegistrazione']) ?>
                        </p>
                        <div class="modify-actions">
                            <form method="GET" action="notizia.php">
                                    <input type="hidden" name="id" value= "<?= htmlspecialchars($row['Id_articolo'])?>" >
                                    <input type="hidden" value= "visualizza" >
                                    <button type="submit" class="modify visualizza" > visualizza</button>
                            </form>
                            <button type="button" class="modify elimina" onclick='confermaEliminazione()'>
                              elimina
                            </button>
                            <button type="button" class="modify modifica"
                                  onclick='apriPopup(
                                    <?= $row["Id_articolo"] ?>,
                                    <?= json_encode($row["titolo"], JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
                                    <?= json_encode($row["sottotitolo"], JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
                                    <?= json_encode($row["contenuto"], JSON_HEX_APOS | JSON_HEX_QUOT) ?>
                              )'>
                              modifica
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>

            </div>

        <?php endforeach; ?>
    </div>

<?php endif; ?>
        </div>

      <footer class="app-footer">
        <span>© <?php echo date('Y'); ?> – News Progect</span>
      </footer>

      
    </section>
  </main>
  <!-- Popup modifica -->
<div class="popup-overlay" id="popupModifica">
  <div class="popup">
    <h3>Modifica articolo</h3>
    <form method="POST" id="formModifica">
      <input type="hidden" name="action" value="modifica">
      <input type="hidden" name="id"     id="popupId">

      <div class="field">
        <label>Titolo</label>
        <input type="text" name="titolo" id="popupTitolo">
      </div>
      <div class="field">
        <label>Sottotitolo</label>
        <input type="text" name="sottotitolo" id="popupSottotitolo">
      </div>
      <div class="field">
        <label>Contenuto</label>
        <textarea name="contenuto" id="popupContenuto"></textarea>
      </div>

      <div class="popup-actions">
        <button type="button" class="btn" onclick="chiudiPopup()">Annulla</button>
        <button type="submit" class="btn primary">Salva</button>
      </div>
    </form>
  </div>
</div>
  <!-- Popup elimina -->
<div class="popup-overlay" id="popupElimina">
  <div class="popup">
    <h3>confermi di voler eliminare questo articolo?</h3>
    <label>L'azione è irreversibile</label>
    <form method="POST" action="">
        <input type="hidden" name="id" value= "<?= htmlspecialchars($row['Id_articolo'])?>" >
        <input type="hidden" name="action" value= "delete" >
        <div class="popup-actions">
            <button type="button" class="btn" onclick="chiudiElimina()">Annulla</button>
            <button type="submit" class="btn primary">Elimina</button>
      </div>
    </form>
  </div>
</div>
  <script>
    function confermaEliminazione() {
        document.getElementById('popupElimina').classList.add('aperto');
    }

    function chiudiElimina() {
        document.getElementById('popupElimina').classList.remove('aperto');
    }

    document.getElementById('popupElimina').addEventListener('click', function(e) {
        if (e.target === this) chiudiElimina();
    });

    function apriPopup(id, titolo, sottotitolo, contenuto) {
        document.getElementById('popupId').value          = id;
        document.getElementById('popupTitolo').value      = titolo;
        document.getElementById('popupSottotitolo').value = sottotitolo;
        document.getElementById('popupContenuto').value   = contenuto;
        document.getElementById('popupModifica').classList.add('aperto');
    }

    function chiudiPopup() {
        document.getElementById('popupModifica').classList.remove('aperto');
    }

    // Chiudi cliccando fuori dal popup
    document.getElementById('popupModifica').addEventListener('click', function(e) {
        if (e.target === this) chiudiPopup();
    });

    function toggleLogout() {
        var menu = document.getElementById("logout-menu");
        if (menu.style.display === "none"|| menu.style.display === '') {
            menu.style.display = "block";;
        } else {
        menu.style.display = "none";;
        }
    }

    window.onclick = function(event) {
        if (!event.target.closest('.user-badge')) {
            document.getElementById("logout-menu").style.display = "none";
        }
    }

</script>
</body>