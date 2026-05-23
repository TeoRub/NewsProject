<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/DB.php';

$logged = isset($_SESSION['stato']) && $_SESSION['stato'] === 1;
$userName  = $_SESSION['user_name']  ?? 'Utente';
$userEmail = $_SESSION['user_email'] ?? '';
$role      = $_SESSION['role']       ?? '';
$User_name = $_SESSION['UserName']   ?? 'Tu';

$byData = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? null;
    $email = isset($_POST["email"]) ? (string)$_POST["email"] : null;
    if ($action === "update" && $email !== null) {
        querymodifica($pdo,$email);
    }
    if($action === "aggiungi"){
      $update2="UPDATE utenti SET ruolo = 'gestore' WHERE Email = :email";
      $stmt2 = $pdo->prepare($update2);
      $stmt2->execute([":email"=> $_POST["gestori"]]);
    }
    if($action === "aggiungiA"){
      $update2="UPDATE utenti SET ruolo = 'ADMIN' WHERE Email = :email";
      $stmt2 = $pdo->prepare($update2);
      $stmt2->execute([":email"=> $_POST["gestori"]]);
    }

    header("Location: home.php");
    exit;
}
if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $id= $_GET["Nome"] ?? 0;
         if($id > 0){
  $articoli ="SELECT  Id_articolo,titolo,sottotitolo,DataRegistrazione,u.User_name,u.Nome,u.Cognome
                FROM articoli a
                JOIN utenti u ON a.id_utente = u.id
                WHERE u.id = :id
                AND a.modify = 0
                ORDER BY a.DataRegistrazione";
  $stmt = $pdo->prepare($articoli);
  $stmt->execute([":id"=> $id]);
  $trovati = $stmt->fetchAll();
  }else{
    $articoli ="SELECT  Id_articolo,titolo,sottotitolo,DataRegistrazione,contenuto,u.User_name,u.Nome,u.Cognome
                FROM articoli a
                JOIN utenti u ON a.id_utente = u.id
                WHERE a.modify = 0
                ORDER BY a.DataRegistrazione";
    $tut = $pdo->prepare($articoli);
    $tut->execute();
    $trovati = $tut->fetchAll();
  }
  foreach ($trovati  as $row) {
    $byData[$row['DataRegistrazione']][] = $row;
}

}

function querymodifica(PDO $pdo, string $email){
    $update="UPDATE utenti SET Stato = 1 WHERE Email = :email";
    $stmt = $pdo->prepare($update);
    $stmt->execute([":email"=> $email]);
}

$pw=$pdo->prepare("SELECT Email,Nome,Cognome 
                        FROM Utenti 
                        WHERE stato = 0");
$pw->execute();
$utente=$pw->fetchAll();

$em = $pdo->prepare("SELECT id,Email,Nome,Cognome,ruolo FROM Utenti WHERE stato = 1 AND ruolo = 'Publisher'");
$em->execute();
$utenti_trovati = $em->fetchAll();

$em = $pdo->prepare("SELECT id,Email,Nome,Cognome,ruolo FROM Utenti WHERE stato = 1 AND ruolo = 'gestore'");
$em->execute();
$utenti_trovatiG = $em->fetchAll();

$em = $pdo->prepare("SELECT u.id,u.Email,u.Nome,u.Cognome,u.ruolo FROM Utenti u WHERE u.stato = 1 AND EXISTS(SELECT 1 FROM articoli a WHERE a.id_utente=u.id )");
$em->execute();
$dati = $em->fetchAll();
?>
<!doctype html>
<html lang="it">
<head>
  <?php require_once __DIR__ . '/head.php'; ?>
  <meta charset="utf-8">
  <title>Portale dello scrittore - Home</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="stylesheet" href="assets/style.css?v=<?php echo filemtime('assets/style.css'); ?>">
</head>

<body>
  <main class="page">
    <section class="app-card">

      <header class="app-header">
        <div class="mark" aria-hidden="true"></div>
        <div class="app-header-text">
          <h1>Portale dello scrittore</h1>
          <p class="subtitle">Visualizza tutte le novità disponibili</p>
        </div>

        <?php if ($logged): ?>
          <div class="user-badge" onclick="toggleLogout()" style="cursor: pointer; position: relative;">
            <div class="avatar">
              <?php
                $initial = mb_substr(trim($userName), 0, 1, 'UTF-8');
                echo htmlspecialchars(mb_strtoupper($initial, 'UTF-8'));
              ?>
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
                
              </div><br>

              <?php if ($logged && ($role === 'ADMIN' || $role === 'gestore')): ?>
                <?php if(count($utente) > 0): ?>
                  <div style="display:flex; align-items:center; gap:10px; margin-bottom:18px; flex-wrap:wrap;">
                    <span style="font-size:15px; font-weight:700;">utenti in attesa</span>
                    <span class="pill accent"><?= count($utente) ?> totali</span>
                  </div>
                  <button type="button"  class="pill red" onclick='apriPopup("A")'> acceta scrittori </button>
                <?php endif; ?>
                <?php if ($role === 'ADMIN'): ?>
                  <button type="button" class="pill red" onclick='apriPopup("I")'> pannello amministratore </button>
                <?php endif; ?>
              <?php endif; ?>
              
            </div>
          </div>


        <?php else: ?>
          <div class="actions center">
            <a class="btn primary" href="login.php">Accedi</a>
          </div>
        <?php endif; ?>
      </header>

      <div class="app-body">
          
          <div class="notice">
            <div>
            <form method="get">
              <select name="Nome" id="listaUtenti">
                <option  value=0>tutti </option>
                <?php foreach ($dati as $u): ?>
                  <option  value="<?= $u['id'] ?>"><?= htmlspecialchars($u['Nome']) ?> <?= htmlspecialchars($u['Cognome']) ?></option>
                <?php endforeach; ?>
              </select>
              <button type="submit">cerca </button>
            </form>
          </div>
            <div id="lista-articoli">
              
            <?php foreach ($byData as $data => $rows): ?>

              <div class="date-group">
                <div class="date-label" style="font-weight:700; margin: 16px 0 8px;">
                    <?= htmlspecialchars($data) ?>
                </div>

                <?php foreach ($rows as $row): ?>
                    <div class="notice" style="border:1px solid #ccc; padding:10px; margin:10px 0; border-radius:8px;">
                        <p><strong>Titolo:</strong> <?= htmlspecialchars($row['titolo']) ?></p>
                        <p><strong>Sottotitolo:</strong> <?= htmlspecialchars($row['sottotitolo']) ?></p>
                        <p><strong>Autore:</strong> <?= htmlspecialchars($row['Nome']) ?> <?= htmlspecialchars($row['Cognome']) ?> - <?= htmlspecialchars($row['User_name']) ?></p>
                        <p style="font-size:12px; color:#888;">
                            Pubblicato il <?= htmlspecialchars($row['DataRegistrazione']) ?>
                        </p>
                        <form method="GET" action="notizia.php">
                                <input type="hidden" name="id" value= "<?= htmlspecialchars($row['Id_articolo'])?>" >
                                <button type="submit" class="pill red" > visualizza</button>
                        </form>
                    </div>
                <?php endforeach; ?>

              </div>

              <?php endforeach; ?>
            </div>
          </div>

        
      </div>

      <footer class="app-footer">
        <span>© <?php echo date('Y'); ?> – News progect</span>
      </footer>

    </section>
  </main>
    <!-- Popup -->
     <div class="popup-overlay" id="popupAccetta">
      <div class="popup">
        <?php if ($logged && ($role === 'ADMIN' || $role === 'gestore')): ?>
          <?php if(count($utente) > 0): ?>
            <br>
            <section class="card">
              <div class="card-head">
                <h3>utenti in attesa</h3>
              </div>
              <div class="card-body">
                    <?php foreach ($utente as $u): ?>
                      <div class="notice" style="border: 1px solid #ccc; padding: 10px; margin: 10px 0;">
                        <p><strong>Nome:</strong> <?= htmlspecialchars($u['Nome']) ?> <?= htmlspecialchars($u['Cognome']) ?></p>
                        <p><strong>Email:</strong> <?= htmlspecialchars($u['Email']) ?></p>
                        <form method="POST" novalidate> 
                                <input type="hidden" name="action" value="update" >
                                <input type="hidden" name="email" value= "<?= htmlspecialchars($u['Email'])?>" >
                                <button type="submit" class="pill red" > accetta</button>
                        </form>
                      </div>
                    <?php endforeach; ?>
              </div>
            </section>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>
    <div class="popup-overlay" id="popup">
      <div class="popup">
        <?php if ($role === 'ADMIN'): ?> 
          <?php if(count($utenti_trovati) > 0): ?>
            <section class="card">
                  <div class="card-head">
                  <h3>aggiungi gestori</h3>
                </div>
                <div class="card-body">
                <form method="post">
                  <select name="gestori" id="listaUtenti">
                    <?php foreach ($utenti_trovati as $u): ?>
                      <option  value="<?= $u['Email'] ?>"><?= htmlspecialchars($u['Nome']) ?> <?= htmlspecialchars($u['Cognome']) ?> - <?= htmlspecialchars($u['Email']) ?></option>
                    <?php endforeach; ?>
                  </select>
                  <input type="hidden" name="action" value="aggiungi" >
                  <button type="submit">aggiungi </button>
                </form>
              </div>
            </section>
            <br>
            <section class="card">
                  <div class="card-head">
                  <h3>rendi ADMIN un gestore</h3>
                </div>
                <div class="card-body">
                <form method="post">
                  <select name="gestori" id="listaUtenti">
                    <?php foreach ($utenti_trovatiG as $u): ?>
                      <option  value="<?= $u['Email'] ?>"><?= htmlspecialchars($u['Nome']) ?> <?= htmlspecialchars($u['Cognome']) ?> - <?= htmlspecialchars($u['Email']) ?></option>
                    <?php endforeach; ?>
                  </select>
                  <input type="hidden" name="action" value="aggiungiA" >
                  <button type="submit">aggiungi </button>
                </form>
              </div>
            </section>
          <?php endif; ?>
        <?php endif;?>
      </div>
    </div>
<script>
function toggleLogout() {
    var menu = document.getElementById("logout-menu");
    if (menu.style.display === "none"|| menu.style.display === '') {
    menu.style.display = "block";;
    } else {
    menu.style.display = "none";;
    }
}
function apriPopup(nome) {
  if(nome == "I")
    document.getElementById('popup').classList.add('aperto');
  else
    document.getElementById('popupAccetta').classList.add('aperto');
}

function chiudiPopup(nome) {
  if(nome == "I")
    document.getElementById('popup').classList.remove('aperto');
  else
    document.getElementById('popupAccetta').classList.remove('aperto');

}

// Chiudi cliccando fuori dal popup
document.getElementById('popup').addEventListener('click', function(e) {
    if (e.target === this) chiudiPopup("I");
});

document.getElementById('popupAccetta').addEventListener('click', function(e) {
    if (e.target === this) chiudiPopup("A");
});

window.onclick = function(event) {
    if (!event.target.closest('.user-badge')) {
        document.getElementById("logout-menu").style.display = "none";
    }
}
</script>

</body>


</html>
<!-- crypto.subtle.digest   per fre il digest in js
async function generateHash(message) {
  // Converte la stringa in bytes
  const msgBuffer = new TextEncoder().encode(message);
  // Calcola l'hash SHA-256
  const hashBuffer = await crypto.subtle.digest('SHA-256', msgBuffer);
  // Converte i bytes in una stringa esadecimale
  const hashArray = Array.from(new Uint8Array(hashBuffer));
  const hashHex = hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
  return hashHex;
}
  

per hash in php 
$password = "la_tua_password_segreta";
// Genera un hash sicuro
$hash = password_hash($password, PASSWORD_DEFAULT);

// Per verificare la password in fase di login
if (password_verify($password, $hash)) {
    echo "Password corretta!";
}


-->