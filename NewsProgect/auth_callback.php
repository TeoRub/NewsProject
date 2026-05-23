<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/DB.php';


if (isset($_SESSION['user_email'])){
  $email= $_SESSION['user_email'];
  $user=$pdo->prepare("SELECT Nome,Cognome,Stato,ruolo,User_name FROM Utenti
                        WHERE Email=:email");
  $user->execute([":email"=>$email]);
  $result=$user->fetch();

  $_SESSION['user_name'] =  mb_strtoupper($result["Nome"]." ".$result["Cognome"]);
  $_SESSION['stato']= $result["Stato"];
  $_SESSION['role']= $result['ruolo'] ?? 'Publisher';
  $_SESSION['UserName']= $result["User_name"] ?? 'Tu';

} else{
  $User_name = $_SESSION['UserName'];
  $user=$pdo->prepare("SELECT Nome,Cognome,Stato,ruolo,Email FROM Utenti
                        WHERE User_name=:UN");
  $user->execute([":UN"=>$User_name]);
  $result=$user->fetch();

  $_SESSION['user_name'] =  mb_strtoupper($result["Nome"]." ".$result["Cognome"]);
  $_SESSION['stato']= $result["Stato"];
  $_SESSION['role']= $result['ruolo'] ?? 'Publisher';
  $_SESSION['user_email']= $result["Email"] ?? '';

}


  header("Location: home.php");
  exit;


?>