-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Versione del server: 10.4.32-MariaDB
-- Versione PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
--
-- Database: `NewsProgect`
--

CREATE Database `NewsProgect`;

USE NewsProgect;

-- --------------------------------------------------------

--
-- Struttura della tabella `articoli`
--

CREATE TABLE `articoli` (
  `Id_articolo` int(11) NOT NULL,
  `titolo` text NOT NULL,
  `sottotitolo` text NOT NULL,
  `contenuto` text NOT NULL,
  `id_utente` int(11) NOT NULL,
  `DataRegistrazione` timestamp NOT NULL DEFAULT current_timestamp(),
  `modify` BOOLEAN NOT NULL DEFAULT FALSE,
  `descrizione` TEXT
);



-- --------------------------------------------------------

--
-- Struttura della tabella `utenti`
--

CREATE TABLE `utenti` (
  `Id` int(11) NOT NULL,
  `Stato` int(11) DEFAULT 0,
  `Nome` varchar(255) DEFAULT NULL,
  `Cognome` varchar(255) DEFAULT NULL,
  `User_name` varchar(255) DEFAULT NULL,
  `Email` varchar(255) NOT NULL,
  `hashpw` varchar(255) DEFAULT NULL,
  `ruolo` varchar(11) DEFAULT 'Publisher'
);

--
-- Dump dei dati per la tabella `utenti`
--

INSERT INTO `utenti` (`Id`, `Stato`, `Nome`, `Cognome`, `User_name`, `Email`, `hashpw`, `ruolo`) VALUES
(1, 1, 'Nome', 'Cognome', 'UserName', 'Email', 'tzFQnX2BfOeIZNC1yMSmAeeuPE0hWhHKNZCT5ingvDAmUFMxG2JT6', 'ADMIN'),
----- da inserire il nome, cognomme, username , email prima importare il db per inizializzare il proprio account come amministratire 
----- la passaword di base è ADMIN123 da cambiare al primo accesso 
--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `articoli`
--
ALTER TABLE `articoli`
  ADD PRIMARY KEY (`Id_articolo`),
  ADD KEY `fk_utente` (`id_utente`);

--
-- Indici per le tabelle `utenti`
--
ALTER TABLE `utenti`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD UNIQUE KEY `User_name` (`User_name`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `articoli`
--
ALTER TABLE `articoli`
  MODIFY `Id_articolo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT per la tabella `utenti`
--
ALTER TABLE `utenti`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `articoli`
--
ALTER TABLE `articoli`
  ADD CONSTRAINT `fk_utente` FOREIGN KEY (`id_utente`) REFERENCES `utenti` (`Id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
