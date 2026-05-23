# Progetto News - Piattaforma di Gestione Articoli

Una piattaforma web dinamica sviluppata in **PHP** e **MySQL** per la gestione e la pubblicazione di articoli giornalistici o blog post. Il sistema include la gestione degli utenti con ruoli differenti

## Prerequisiti

Per eseguire questo progetto in locale, assicurati di avere installato:
* **XAMPP** (consigliato con PHP 8.2 o superiore)
* **MySQL / MariaDB**
* Un client Git (opzionale, per clonare la repository)

---

## Installazione e Configurazione

Segui questi passaggi per configurare il progetto sul tuo computer locale utilizzando XAMPP:

### 1. Posizionamento dei file
Clona la repository o estrai i file all'interno della cartella dei documenti di XAMPP:
```bash
C:\xampp\htdocs\NewsProgect\ 
```
L'URL locale per accedere al progetto sarà: http://localhost/NewsProgect/

### 2. Configurazione del Database

#### (Account Amministratore)

#### ⚠️ Importante: Prima di effettuare il caricamento, ricordati di modificare nel database ( file DB.sql, tabella utenti) i campi Nome, Cognome, User_name ed Email per associare l'account ai tuoi dati reali.

* Apri il pannello di controllo di XAMPP e avvia i moduli Apache e MySQL.

* Apri il browser e vai su phpMyAdmin.

* vai nella scheda Importa e seleziona il file DB.sql incluso nel progetto per generare le tabelle e i vincoli.

### 3. Configurazione del file config.php
* Apri il file config.php e verifica che i parametri di connessione al database corrispondano alla tua configurazione XAMPP:
```shell
define('DB_HOST', 'localhost');
define('DB_NAME', 'NewsProgect');
define('DB_USER', 'root');
define('DB_PASS', ''); // Di base su XAMPP la password è vuota
```

### primo accesso al portale dello scrittore 
* accedi con la mail o username inserita nel Fie DB.sql nel 2°passaggio
* usa la password provvisoria ADMIN123
* cambia la password di default per motivi di sicurezza

### Struttura del Database
Il database progettoNews è composto da due tabelle principali legate da una relazione di chiave esterna:

* utenti: Contiene le informazioni personali degli utenti, l'hash della password, lo stato dell'account e il ruolo assegnato.

* articoli: Contiene il testo dei post (titolo, sottotitolo, contenuto), la data di registrazione e l'id dell'autore (id_utente) collegato alla tabella utenti.