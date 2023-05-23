-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Creato il: Mag 23, 2023 alle 09:42
-- Versione del server: 10.4.28-MariaDB
-- Versione PHP: 8.0.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `finanza`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `investimenti`
--

CREATE TABLE `investimenti` (
  `id` int(11) NOT NULL,
  `userName` varchar(255) DEFAULT NULL,
  `ticker` varchar(255) DEFAULT NULL,
  `quantity` decimal(10,2) DEFAULT NULL,
  `purchasePrice` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `investimenti`
--

INSERT INTO `investimenti` (`id`, `userName`, `ticker`, `quantity`, `purchasePrice`, `created_at`) VALUES
(1, 'bestia', 'JPM', 1.00, 139.18, '2023-05-22 10:48:58'),
(2, 'bestia', 'JPM', 2.00, 139.78, '2023-05-22 14:01:52'),
(3, 'bestia', 'JPM', 2.00, 139.78, '2023-05-22 14:03:38'),
(12, 'mana', 'AAPL', 1.00, 174.25, '2023-05-22 20:57:09'),
(13, 'mana', 'GOOGL', 1.00, 125.06, '2023-05-22 20:57:13');

-- --------------------------------------------------------

--
-- Struttura della tabella `preferiti`
--

CREATE TABLE `preferiti` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `ticker` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `preferiti`
--

INSERT INTO `preferiti` (`id`, `username`, `ticker`) VALUES
(42, 'a', 'JNJ'),
(43, 'a', 'MA'),
(44, 'mana', 'AAPL'),
(45, 'mana', 'GOOGL');

-- --------------------------------------------------------

--
-- Struttura della tabella `saldo_utenti`
--

CREATE TABLE `saldo_utenti` (
  `id` int(11) NOT NULL,
  `userName` varchar(255) NOT NULL,
  `saldo` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `saldo_utenti`
--

INSERT INTO `saldo_utenti` (`id`, `userName`, `saldo`) VALUES
(1, 'mana', 1169.31),
(2, 'polo', 1000.00),
(3, 'ciao', 1000.00),
(4, 'a', 1000.00);

-- --------------------------------------------------------

--
-- Struttura della tabella `user`
--

CREATE TABLE `user` (
  `ID` int(11) UNSIGNED NOT NULL,
  `userName` varchar(30) NOT NULL,
  `pass` varchar(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dump dei dati per la tabella `user`
--

INSERT INTO `user` (`ID`, `userName`, `pass`) VALUES
(4, 'mana', '$2y$10$6bg/GPS5xsb6YcX1rERruepIfIPx3dpwn4U2bkNZslQ3B7zq3Fa3y'),
(7, 'a', '$2y$10$hgTgM53FefilHE8Aruh34u3S8XLu0GEYNF9erXpx/92UdQSJKQFBy');

--
-- Trigger `user`
--
DELIMITER $$
CREATE TRIGGER `set_initial_balance` AFTER INSERT ON `user` FOR EACH ROW BEGIN
  INSERT INTO saldo_utenti (userName, saldo) VALUES (NEW.userName, 1000);
END
$$
DELIMITER ;

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `investimenti`
--
ALTER TABLE `investimenti`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `preferiti`
--
ALTER TABLE `preferiti`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `saldo_utenti`
--
ALTER TABLE `saldo_utenti`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`ID`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `investimenti`
--
ALTER TABLE `investimenti`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT per la tabella `preferiti`
--
ALTER TABLE `preferiti`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT per la tabella `saldo_utenti`
--
ALTER TABLE `saldo_utenti`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT per la tabella `user`
--
ALTER TABLE `user`
  MODIFY `ID` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
