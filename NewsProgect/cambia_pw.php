<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

$logged = isset($_SESSION['stato']) && $_SESSION['stato'] === 1;
$userName  = $_SESSION['user_name']  ?? 'Utente';
$userEmail = $_SESSION['user_email'] ?? '';
$role      = $_SESSION['role']       ?? '';

if (!$logged) {
  header("Location: home.php");
  exit;
}
$_POST["state"]=true;
$_POST["state2"]=2;
try{
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $password = $_POST["hash"];
  // Genera un hash sicuro
  $hash = password_hash($password, PASSWORD_DEFAULT);
  $pw  = $pdo->prepare("UPDATE utenti SET hashpw = :hashpw WHERE Email = :email");
  $success=$pw->execute([":hashpw"=>$hash,":email"=>$userEmail]);
  if ($success) {
    // Registrazione riuscita, ora puoi mandarlo al login o loggarlo subito
    header("Location: home.php");
    exit;
  } else {
    $_POST["state"]=false;
  }
}
}catch (PDOException $e) {
    // Codice errore MySQL per duplicate entry
    if ($e->errorInfo[1] == 1062) {
      $_POST["state2"]=1;
        //echo "Questa email è già registrata";
    } else {
        // Log errore tecnico
        error_log($e->getMessage());
        $_POST["state2"]=0;
        //echo "Errore interno, riprova più tardi";
    }
}
?>
<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <title>Login - Prenotazione Aule</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- CSS esterno -->
  <link rel="stylesheet" href="assets/style.css?v=5">
  <!-- importai svg -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">


</head>

<body class='auth'>
  <main class="page">
    <section class="login-card">
     <header class="login-header">
      <div class="header-row">
        <a class="linkrow" href="./"><div class="mark" aria-hidden="true"></div></a>

        <div class="header-text">
          <h1>Cambia password</h1>
          <p>Portale degli scrittori</p>
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
                Errore durante la registrazione. Riprova.
            </div>
          <?php endif; ?>
          <?php if($_POST["state2"]===1): ?>
            <div class="login-error">
                Errore questa email è già registrata.
            </div>
          <?php elseif($_POST["state2"]===0): ?>
          <div class="login-error">
                Errore interno, riprova più tardi.
            </div>
          <?php endif; ?>
        <div class="info">
            <form id="aut" method="POST" novalidate >
                <div class="field">
                    <label for="password">password:</label>
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

                <div class="field">
                    <label for="password">conferma password:</label>
                    <p style="display: flex; align-items: center; gap: 10px;">
                        <input type="password" id="cpw" name="cpw" required>
                        <span id="eye-icon" style="cursor: pointer; font-size: 1.2em; user-select: none;"
                            onclick="if(cpw.type === 'password'){ cpw.type = 'text'; this.innerHTML = '<i class=\'fa fa-eye\' aria-hidden=\'true\'></i>'; } else { cpw.type = 'password'; this.innerHTML = '<i class=\'fa fa-eye-slash\' aria-hidden=\'true\'></i>'; }"
                            ontouchstart="event.preventDefault(); if(cpw.type === 'password'){ cpw.type = 'text'; this.innerHTML = '<i class=\'fa fa-eye\' aria-hidden=\'true\'></i>'; } else { cpw.type = 'password'; this.innerHTML = '<i class=\'fa fa-eye-slash\' aria-hidden=\'true\'></i>'; }">
                          <i class="fa fa-eye-slash" aria-hidden="true"></i>
                        </span><!--<span style="cursor: pointer; font-size: 1.2em; user-select: none;"
      onmousedown="pw.type = 'text'; this.innerHTML =`<i class=\'fa fa-eye\' aria-hidden=\'true\'></i>`" 
      onmouseup="pw.type = 'password'; this.innerHTML =`<i class=\'fa fa-eye-slash\' aria-hidden=\'true\'></i>`"
      onmouseout="pw.type = 'password'; this.innerHTML =`<i class=\'fa fa-eye-slash\' aria-hidden=\'true\'></i>`"
      ontouchstart="event.preventDefault(); pw.type = 'text'; this.innerHTML =`<i class=\'fa fa-eye\' aria-hidden=\'true\'></i>`"
      ontouchend="pw.type = 'password'; this.innerHTML =`<i class=\'fa fa-eye-slash\' aria-hidden=\'true\'></i>`">
      <i class="fa fa-eye-slash" aria-hidden="true"></i>
</span>
-->
                    </p>
                </div>

             <div class="login-actions">
               <input type="button" id="btn" class="btn solid" value="cambia password">
              </div>
            </form>
        </div>
      </div>

      <footer class="login-footer">
        <span>© <?php echo date('Y'); ?> – News Progect</span>
      </footer>
    </section>
  </main>
  <script>
     document.getElementById("btn").addEventListener('click', () => {
      // Qui fai i tuoi controlli personalizzati
      const valore = document.getElementById('pw').value;
      if(valore == document.getElementById('cpw').value){
        generateHash(valore)
      }else{
        alert("password diverse")
      }
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
      return ;
    }
  </script>
</body>
</html>