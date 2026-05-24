<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

if (isset($_SESSION['stato']) && $_SESSION['stato'] === 1) {
  header("Location: home.php");
  exit;
}
$_POST["accettato"]=0;
$_POST["state"]=true;
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  if($_POST["action"]==="accetta"){
    header("Location: home.php");
    exit;
  }
  $password = $_POST["hash"];
  $email =$_POST["email"];
  // Genera un hash sicuro
  if (filter_var($email, FILTER_VALIDATE_EMAIL)){
    $st  = $pdo->prepare("SELECT stato 
                        FROM Utenti 
                        WHERE Email = :email");
  } else {
    $st  = $pdo->prepare("SELECT stato 
                        FROM Utenti 
                        WHERE User_name = :email");
  }
  $st->execute([":email" => $email]);
  $stato=$st->fetch();
  if($stato["stato"]===1){
    $_POST["accettato"]=1;
  }else{
    $_POST["accettato"]=2;
  }
  if($_POST["accettato"]===1){
    if (filter_var($email, FILTER_VALIDATE_EMAIL)){
      $pw  = $pdo->prepare("SELECT hashpw 
                          FROM Utenti 
                          WHERE stato = 1
                          AND Email = :email");
    } else {
      $pw  = $pdo->prepare("SELECT hashpw 
                          FROM Utenti 
                          WHERE stato = 1
                          AND User_name = :email");
    }
    $pw->execute([":email" => $email]);
    $utente=$pw->fetch();
  }
  // Per verificare la password in fase di login
  if($_POST["accettato"]===1){
    if ($utente && password_verify($password, $utente["hashpw"])) {
      if (filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)){$_SESSION['user_email'] = $_POST["email"];}
      else{$_SESSION['UserName'] = $_POST["email"];}
      header("Location: auth_callback.php");
      exit;
    }
    else{
      $_POST["state"]=false;
    }
  }
}
?>
<!doctype html>
<html lang="it">
<head>
  <?php require_once __DIR__ . '/head.php'; ?>
  <meta charset="utf-8">
  <title>Portale dello scrittore - Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- CSS esterno -->
  <link rel="stylesheet" href="assets/style.css?v=2">
  <!-- importai svg -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

</head>

<body class="auth">
  <main class="page">
    <section class="login-card">
     <header class="login-header">
  <div class="header-row">
    <a class="linkrow" href="./"><div class="mark" aria-hidden="true"></div></a>

    <div class="header-text">
      <h1>Portale dello scrittore</h1>
    
    </div>
  </div>
</header>
      <div class="login-body">
      <!-- Bottone indietro -->
      <a href="home.php" style="display:inline-flex;align-items:center;gap:6px;font-size:13px;color:var(--accent);text-decoration:none;margin-bottom:24px;">
        ← Torna alla home
      </a>

        <?php if(!$_POST["state"]): ?>
            <div class="login-error">
              Credenziali errate. Riprova.
            </div>
        <?php endif; ?>
        <?php if($_POST["accettato"]===2): ?>
            <div class="login-error">
              <h3>la tua registrazione è stata notificata all' amministratore</h3>
              <p>Potrai accedere solo dopo che l'amministratore ti avrà accettato</p>
            </div>
        <?php endif; ?>
            <form id="aut" method="POST">
              <input type="hidden" name="action"            value="login">
              <div class="field">
                <label for="email">Email o UserName:<span class="req">*</span></label>
                <input type="email" id="email" name="email" required>
              </div>

              <div class="field">
                <label for="pw">password:<span class="req">*</span></label>
                <p style="display: flex; align-items: center; gap: 10px;">
                  <input type="password" id="pw" name="pw" required>
                  <span id="eye-icon" style="cursor: pointer; font-size: 1.2em; user-select: none;"
                      onclick="if(pw.type === 'password'){ pw.type = 'text'; this.innerHTML = '<i class=\'fa fa-eye\' aria-hidden=\'true\'></i>'; } else { pw.type = 'password'; this.innerHTML = '<i class=\'fa fa-eye-slash\' aria-hidden=\'true\'></i>'; }"
                      ontouchstart="event.preventDefault(); if(pw.type === 'password'){ pw.type = 'text'; this.innerHTML = '<i class=\'fa fa-eye\' aria-hidden=\'true\'></i>'; } else { pw.type = 'password'; this.innerHTML = '<i class=\'fa fa-eye-slash\' aria-hidden=\'true\'></i>'; }">
                    <i class="fa fa-eye-slash" aria-hidden="true"></i>
                  </span>
                </p>
                <input type="hidden" name="hash" id="hash" value="">
              </div>

              <div class="login-actions">
                <input type="button" id="btn" class="btn solid" value="login">
                <input type="button" id="btn2" class="btn" onclick="window.location='register.php'" value="register">
              </div>
              </form>
      </div>

      <footer class="login-footer">
        <span>© <?php echo date('Y'); ?> – News Progect</span>
      </footer>
    </section>
  </main>


  <div class="popup-overlay" id="popup">
  <div class="popup">
    <h3>la tua registrazione verrà notificata all' amministratore</h3>
    <label>Potrai accedere solo dopo che l'amministratore ti avrà accettato</label>
        <form method="POST" action="">
        <input type="hidden" name="action" value= "accetta" >
        <div class="popup-actions">
            <button type="submit" class="btn primary">OK</button>
      </div>
    </form>
  </div>
</div>
  <script>
     document.getElementById("btn").addEventListener('click', () => {
      // Qui fai i tuoi controlli personalizzati
      const valore = document.getElementById('pw').value;
      generateHash(valore)
  });
    async function generateHash(message) {
      // Converte la stringa in bytes
      const msgBuffer = new TextEncoder().encode(message);
      // Calcola l'hash SHA-256
      const hashBuffer = await crypto.subtle.digest('SHA-256', msgBuffer);
      // Converte i bytes in una stringa esadecimale
      const hashArray = Array.from(new Uint8Array(hashBuffer));
      const hashHex = hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
      document.getElementById('hash').value=hashHex;
      document.getElementById("aut").submit();
      return hashHex;
    }
  </script>
  
  <?php if(isset($_GET["registered"]) && $_GET["registered"]==="1"): ?>
    <script>document.getElementById('popup').classList.add('aperto');</script>
  <?php endif; ?>
</body>
</html>