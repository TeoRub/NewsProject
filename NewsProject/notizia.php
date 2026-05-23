<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/DB.php';

$logged = isset($_SESSION['user_email']);
$userName  = $_SESSION['user_name']  ?? 'user';
$userEmail = $_SESSION['user_email'] ?? '';
$role      = $_SESSION['role']       ?? '';
$User_name = $_SESSION['UserName']   ?? 'Tu';

$id = isset($_GET['id']) ? (int)$_GET['id'] : -1;

if ($id < 0) {
    header("Location: home.php");
    exit;
}
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? null;
    $ID = isset($_POST["id"]) ? (int)$_POST["id"] : -1;
    if ($action === "segnala" && $ID>-1) {
        $segnala="UPDATE articoli a SET a.segnalazione = :segnala,a.modify = 1 WHERE Id_articolo = :id";
        $stmt = $pdo->prepare($segnala);
        $stmt->execute([":segnala"=> $_POST["contenuto"], ":id"=>$ID ]);
    }
    header("location: notizia.php?id=".$ID);
    exit;
}

$stmt = $pdo->prepare("SELECT a.Id_articolo,a.titolo, a.sottotitolo, a.contenuto, a.DataRegistrazione,a.modify,a.segnalazione,
                               u.Nome, u.Cognome, u.User_name
                        FROM articoli a
                        JOIN utenti u ON a.id_utente = u.id
                        WHERE a.Id_articolo = :id");
$stmt->execute([":id" => $id]);
$articolo = $stmt->fetch();

if (!$articolo) {
    header("Location: home.php");
    exit;
}


?>
<!doctype html>
<html lang="it">
<head>
    <?php require_once __DIR__ . '/head.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News</title>
    <link rel="stylesheet" href="assets/style.css?v=2">
</head>
<body>
  <main class="page">
    <section class="app-card">
        <header class="app-header">
            <a class="linkrow" href="./"><div class="mark" aria-hidden="true"></div></a>
            <div class="app-header-text">
                <h1>Portale dello scrittore</h1>
                <p class="subtitle">Leggi nuove notizie ogni giorno</p>
            </div>
            <?php if ($logged): ?>
            <!--<div class="user-badge" style="display:flex;align-items:center;gap:10px;padding:8px 10px;border:1px solid var(--line);border-radius:14px;background:rgba(255,255,255,.70);">
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
                            <a class="btn primary" href="miei_articoli.php">MyArticol</a>
                            <a class="btn" href="logout.php">Logout</a>
                            <a class="btn" href="cambia_pw.php">new PW</a>
                
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </header>

        <div class="app-body">

            <article style="max-width:720px; margin:0 auto;">

                <div id="view-mode">
                    <div>    
                        <!-- Bottone indietro -->
                        <pre><a href="home.php" style="display:inline-flex;align-items:center;gap:6px;font-size:13px;color:var(--accent);text-decoration:none;margin-bottom:24px;">← Torna alla home</a>    <?php if($role==='ADMIN'||$role==='gestore'): ?><a onclick="confermaSegnalazione()" style="display:inline-flex;align-items:center;font-size:13px;color:red;text-decoration:none;margin-bottom:24px;cursor: pointer;">segnala</a><?php endif; ?>    <?php if($articolo['User_name'] === $User_name): ?><a onclick="apriModifica()" style="display:inline-flex;align-items:center;font-size:13px;color:#2980b9;text-decoration:none;margin-bottom:24px;cursor: pointer;">modifica</a>    <a onclick="confermaEliminazione()" style="display:inline-flex;align-items:center;font-size:13px;color:red;text-decoration:none;margin-bottom:24px;cursor: pointer;">elimina</a><?php endif; ?></pre>
                        <?php if($articolo['modify'] === 1): ?> <p><strong style="color:red"><?= htmlspecialchars($articolo['segnalazione']) ?></strong></p> <?php endif; ?>
                    </div>

                    <!-- Titolo -->
                    <h2 style="font-size:clamp(22px,4vw,32px); font-weight:800; line-height:1.2; margin-bottom:8px;">
                        <?= htmlspecialchars($articolo['titolo']) ?>
                    </h2>

                    <!-- Sottotitolo -->
                    <p style="font-size:17px; color:#555; margin-bottom:16px;">
                        <?= htmlspecialchars($articolo['sottotitolo']) ?>
                    </p>

                    <!-- Meta: autore e data -->
                    <div style="display:flex;gap:12px;align-items:center;font-size:13px;color:#888;margin-bottom:28px;padding-bottom:16px;border-bottom:1px solid var(--line);">
                        <span><?= htmlspecialchars($articolo['Nome']) ?> <?= htmlspecialchars($articolo['Cognome']) ?></span>
                        <span>·</span>
                        <span><?= htmlspecialchars($articolo['DataRegistrazione']) ?></span>
                    </div>

                    <!-- Contenuto -->
                    <div style="font-size:16px; line-height:1.8; color:#222;">
                        <?= nl2br(htmlspecialchars($articolo['contenuto'])) ?>
                    </div>
                </div>
                <div id="edit-mode" style="display:none;">
                    <form method="POST" id="formModifica" action="miei_articoli.php">
                        <input type="hidden" name="action" value="modifica">
                        <input type="hidden" name="id"     value="<?= htmlspecialchars($articolo['Id_articolo']) ?>">

                        <div class="field">
                            <label>Titolo</label>
                            <input type="text" name="titolo" value="<?= htmlspecialchars($articolo['titolo']) ?>">
                        </div>
                        <div class="field">
                            <label>Sottotitolo</label>
                            <input type="text" name="sottotitolo" value="<?= htmlspecialchars($articolo['sottotitolo']) ?>">
                        </div>
                        <div class="field">
                            <label>Contenuto</label>
                            <textarea name="contenuto"><?= htmlspecialchars($articolo['contenuto']) ?></textarea>
                        </div>

                        <div class="popup-actions" style="margin-top: 20px;">
                            <button type="button" class="btn" onclick="chiudiModifica()">Annulla</button>
                            <button type="submit" class="btn primary">Salva</button>
                        </div>
                    </form>
                </div>
            </article>

        </div>

      <footer class="app-footer">
        <span>© <?php echo date('Y'); ?> – News Progect</span>
      </footer>

    </section>
  </main>
  <!-- Popup elimina -->
<div class="popup-overlay" id="popupElimina">
  <div class="popup">
    <h3>confermi di voler eliminare questo articolo?</h3>
    <label>L'azione è irreversibile</label>
    <form method="POST" action="miei_articoli.php">
        <input type="hidden" name="id" value= "<?= htmlspecialchars($articolo['Id_articolo'])?>" >
        <input type="hidden" name="action" value= "delete" >
        <div class="popup-actions">
            <button type="button" class="btn" onclick="chiudiElimina()">Annulla</button>
            <button type="submit" class="btn primary">Elimina</button>
      </div>
    </form>
  </div>
</div>
<!-- Popup segnalazione -->
<div class="popup-overlay" id="popupSegnala">
  <div class="popup">
    <h3>confermi di voler segnalare questo articolo?</h3>
    <h4>L'azione è irreversibile</h4>
    <form method="POST" action="">
        <input type="hidden" name="id" value="<?= htmlspecialchars($articolo['Id_articolo'])?>" >
        <input type="hidden" name="action" value= "segnala" >
        <div class="field">
        <label>Contenuto della segnalazione</label>
        <textarea name="contenuto" id="contenutoSegnalazione" required></textarea>
      </div>
        <div class="popup-actions">
            <button type="button" class="btn" onclick="chiudiSegnalazione()">Annulla</button>
            <button type="submit" class="btn primary">segnala</button>
      </div>
    </form>
  </div>
</div>
  <script>
    function confermaSegnalazione() {
        document.getElementById('popupSegnala').classList.add('aperto');
    }

    function chiudiSegnalazione() {
        document.getElementById('popupSegnala').classList.remove('aperto');
    }

    document.getElementById('popupSegnala').addEventListener('click', function(e) {
        if (e.target === this) chiudiSegnalazione();
    });

    function confermaEliminazione() {
        document.getElementById('popupElimina').classList.add('aperto');
    }

    function chiudiElimina() {
        document.getElementById('popupElimina').classList.remove('aperto');
    }

    document.getElementById('popupElimina').addEventListener('click', function(e) {
        if (e.target === this) chiudiElimina();
    });

    function apriModifica() {
        document.getElementById('view-mode').style.display = 'none';
        document.getElementById('edit-mode').style.display = 'block';
    }

    function chiudiModifica() {
        document.getElementById('view-mode').style.display = 'block';
        document.getElementById('edit-mode').style.display = 'none';
    }


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