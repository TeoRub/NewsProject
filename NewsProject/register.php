<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

if (isset($_SESSION['user_email'])) {
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
  if (filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
    $pw  = $pdo->prepare("INSERT INTO Utenti (Nome,Cognome,User_name,Email,hashpw ) 
                        VALUE (:nome ,:cognome ,:user_name ,:email ,:hashpw )");
    $success=$pw->execute([":nome"=>$_POST["nome"],":cognome"=>$_POST["cognome"],":user_name"=>$_POST["User_name"],":email"=>$_POST["email"],":hashpw"=>$hash]);
} else {
    $success=false;
}
  if ($success) {
    // Registrazione riuscita, ora puoi mandarlo al login o loggarlo subito
    header("Location: login.php?registered=1");
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
  <?php require_once __DIR__ . '/head.php'; ?>
  <meta charset="utf-8">
  <title>Portale dello scrittore - Register</title>
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
          <h1>Crea un account</h1>
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
                Errore questa email o UserName è già registrata.
            </div>
          <?php elseif($_POST["state2"]===0): ?>
          <div class="login-error">
                Errore interno, riprova più tardi.
            </div>
          <?php endif; ?>
        <div class="info">
            <form id="aut" method="POST" novalidate >
             <div class="form-grid">
              <div class="field">
               <input type="hidden" name="action"            value="register">
               <label for="nome">Nome:</label>
               <input type="text" id="nome" name="nome" placeholder="Inserisci il tuo nome" required>
              </div>

              <div class="field">
               <label for="cognome">cognome:</label>
               <input type="text" id="cognome" name="cognome" placeholder="Inserisci il tuo cognome" required>              </div>

             </div>

              <div class="field">
               <label for="User_name">User_name:</label>
               <input type="text" id="User_name" name="User_name" required>
              </div>

              <div class="field">
               <label for="email">Email:</label>
               <input type="email" id="email"     name="email" required>
              </div>

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

              <div class="login-actions">
               <a href="login.php" class="btn">Hai già un account?</a>
               <input type="button" id="btn" class="btn solid" value="register">
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
      return ;
    }
  </script>
</body>
</html>