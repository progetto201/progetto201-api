-- phpMyAdmin SQL Dump
-- version 4.7.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Creato il: Mag 10, 2020 alle 14:40
-- Versione del server: 10.1.29-MariaDB
-- Versione PHP: 7.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db100_100`
--
CREATE DATABASE IF NOT EXISTS `db100_100` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `db100_100`;

-- --------------------------------------------------------

--
-- Struttura della tabella `t_categories`
--

CREATE TABLE `t_categories` (
  `id` int(11) NOT NULL,
  `description` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dump dei dati per la tabella `t_categories`
--

INSERT INTO `t_categories` (`id`, `description`) VALUES
(1, 'attuatore'),
(0, 'sensore'),
(2, 'switch');

-- --------------------------------------------------------

--
-- Struttura della tabella `t_colors`
--

CREATE TABLE `t_colors` (
  `id` int(11) NOT NULL,
  `color_name` varchar(45) NOT NULL,
  `color_hex` char(7) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dump dei dati per la tabella `t_colors`
--

INSERT INTO `t_colors` (`id`, `color_name`, `color_hex`) VALUES
(1, 'red lighten-5', '#ffebee'),
(2, 'red lighten-4', '#ffcdd2'),
(3, 'red lighten-3', '#ef9a9a'),
(4, 'red lighten-2', '#e57373'),
(5, 'red lighten-1', '#ef5350'),
(6, 'red', '#f44336'),
(7, 'red darken-1', '#e53935'),
(8, 'red darken-2', '#d32f2f'),
(9, 'red darken-3', '#c62828'),
(10, 'red darken-4', '#b71c1c'),
(11, 'red accent-1', '#ff8a80'),
(12, 'red accent-2', '#ff5252'),
(13, 'red accent-3', '#ff1744'),
(14, 'red accent-4', '#d50000'),
(15, 'pink lighten-5', '#fce4ec'),
(16, 'pink lighten-4', '#f8bbd0'),
(17, 'pink lighten-3', '#f48fb1'),
(18, 'pink lighten-2', '#f06292'),
(19, 'pink lighten-1', '#ec407a'),
(20, 'pink', '#e91e63'),
(21, 'pink darken-1', '#d81b60'),
(22, 'pink darken-2', '#c2185b'),
(23, 'pink darken-3', '#ad1457'),
(24, 'pink darken-4', '#880e4f'),
(25, 'pink accent-1', '#ff80ab'),
(26, 'pink accent-2', '#ff4081'),
(27, 'pink accent-3', '#f50057'),
(28, 'pink accent-4', '#c51162'),
(29, 'purple lighten-5', '#f3e5f5'),
(30, 'purple lighten-4', '#e1bee7'),
(31, 'purple lighten-3', '#ce93d8'),
(32, 'purple lighten-2', '#ba68c8'),
(33, 'purple lighten-1', '#ab47bc'),
(34, 'purple', '#9c27b0'),
(35, 'purple darken-1', '#8e24aa'),
(36, 'purple darken-2', '#7b1fa2'),
(37, 'purple darken-3', '#6a1b9a'),
(38, 'purple darken-4', '#4a148c'),
(39, 'purple accent-5', '#ea80fc'),
(40, 'purple accent-6', '#e040fb'),
(41, 'purple accent-7', '#d500f9'),
(42, 'purple accent-4', '#aa00ff'),
(43, 'deep-purple lighten-5', '#ede7f6'),
(44, 'deep-purple lighten-4', '#d1c4e9'),
(45, 'deep-purple lighten-3', '#b39ddb'),
(46, 'deep-purple lighten-2', '#9575cd'),
(47, 'deep-purple lighten-1', '#7e57c2'),
(48, 'deep-purple', '#673ab7'),
(49, 'deep-purple darken-1', '#5e35b1'),
(50, 'deep-purple darken-2', '#512da8'),
(51, 'deep-purple darken-3', '#4527a0'),
(52, 'deep-purple darken-4', '#311b92'),
(53, 'deep-purple accent-1', '#b388ff'),
(54, 'deep-purple accent-2', '#7c4dff'),
(55, 'deep-purple accent-3', '#651fff'),
(56, 'deep-purple accent-4', '#6200ea'),
(57, 'indigo lighten-5', '#e8eaf6'),
(58, 'indigo lighten-4', '#c5cae9'),
(59, 'indigo lighten-3', '#9fa8da'),
(60, 'indigo lighten-2', '#7986cb'),
(61, 'indigo lighten-1', '#5c6bc0'),
(62, 'indigo', '#3f51b5'),
(63, 'indigo darken-1', '#3949ab'),
(64, 'indigo darken-2', '#303f9f'),
(65, 'indigo darken-3', '#283593'),
(66, 'indigo darken-4', '#1a237e'),
(67, 'indigo accent-1', '#8c9eff'),
(68, 'indigo accent-2', '#536dfe'),
(69, 'indigo accent-3', '#3d5afe'),
(70, 'indigo accent-4', '#304ffe'),
(71, 'blue lighten-5', '#e3f2fd'),
(72, 'blue lighten-4', '#bbdefb'),
(73, 'blue lighten-3', '#90caf9'),
(74, 'blue lighten-2', '#64b5f6'),
(75, 'blue lighten-1', '#42a5f5'),
(76, 'blue', '#2196f3'),
(77, 'blue darken-1', '#1e88e5'),
(78, 'blue darken-2', '#1976d2'),
(79, 'blue darken-3', '#1565c0'),
(80, 'blue darken-4', '#0d47a1'),
(81, 'blue accent-1', '#82b1ff'),
(82, 'blue accent-2', '#448aff'),
(83, 'blue accent-3', '#2979ff'),
(84, 'blue accent-4', '#2962ff'),
(85, 'light-blue lighten-5', '#e1f5fe'),
(86, 'light-blue lighten-4', '#b3e5fc'),
(87, 'light-blue lighten-3', '#81d4fa'),
(88, 'light-blue lighten-2', '#4fc3f7'),
(89, 'light-blue lighten-1', '#29b6f6'),
(90, 'light-blue', '#03a9f4'),
(91, 'light-blue darken-1', '#039be5'),
(92, 'light-blue darken-2', '#0288d1'),
(93, 'light-blue darken-3', '#0277bd'),
(94, 'light-blue darken-4', '#01579b'),
(95, 'ight-blue accent-1', '#l80d8f'),
(96, 'light-blue accent-2', '#40c4ff'),
(97, 'light-blue accent-3', '#00b0ff'),
(98, 'light-blue accent-4', '#0091ea'),
(99, 'cyan lighten-5', '#e0f7fa'),
(100, 'cyan lighten-4', '#b2ebf2'),
(101, 'cyan lighten-3', '#80deea'),
(102, 'cyan lighten-2', '#4dd0e1'),
(103, 'cyan lighten-1', '#26c6da'),
(104, 'cyan', '#00bcd4'),
(105, 'cyan darken-1', '#00acc1'),
(106, 'cyan darken-2', '#0097a7'),
(107, 'cyan darken-3', '#00838f'),
(108, 'cyan darken-4', '#006064'),
(109, 'cyan accent-1', '#84ffff'),
(110, 'cyan accent-2', '#18ffff'),
(111, 'cyan accent-3', '#00e5ff'),
(112, 'cyan accent-4', '#00b8d4'),
(113, 'teal lighten-5', '#e0f2f1'),
(114, 'teal lighten-4', '#b2dfdb'),
(115, 'teal lighten-3', '#80cbc4'),
(116, 'teal lighten-2', '#4db6ac'),
(117, 'teal lighten-1', '#26a69a'),
(118, 'teal', '#009688'),
(119, 'teal darken-1', '#00897b'),
(120, 'teal darken-2', '#00796b'),
(121, 'teal darken-3', '#00695c'),
(122, 'teal darken-4', '#004d40'),
(123, 'teal accent-1', '#a7ffeb'),
(124, 'teal accent-2', '#64ffda'),
(125, 'teal accent-3', '#1de9b6'),
(126, 'teal accent-4', '#00bfa5'),
(127, 'green lighten-5', '#e8f5e9'),
(128, 'green lighten-4', '#c8e6c9'),
(129, 'green lighten-3', '#a5d6a7'),
(130, 'green lighten-2', '#81c784'),
(131, 'green lighten-1', '#66bb6a'),
(132, 'green', '#4caf50'),
(133, 'green darken-1', '#43a047'),
(134, 'green darken-2', '#388e3c'),
(135, 'green darken-3', '#2e7d32'),
(136, 'green darken-4', '#1b5e20'),
(137, 'green accent-1', '#b9f6ca'),
(138, 'green accent-2', '#69f0ae'),
(139, 'green accent-3', '#00e676'),
(140, 'green accent-4', '#00c853'),
(141, 'light-green lighten-5', '#f1f8e9'),
(142, 'light-green lighten-4', '#dcedc8'),
(143, 'light-green lighten-3', '#c5e1a5'),
(144, 'light-green lighten-2', '#aed581'),
(145, 'light-green lighten-1', '#9ccc65'),
(146, 'light-green', '#8bc34a'),
(147, 'light-green darken-1', '#7cb342'),
(148, 'light-green darken-2', '#689f38'),
(149, 'light-green darken-3', '#558b2f'),
(150, 'light-green darken-4', '#33691e'),
(151, 'light-green accent-1', '#ccff90'),
(152, 'light-green accent-2', '#b2ff59'),
(153, 'light-green accent-3', '#76ff03'),
(154, 'light-green accent-4', '#64dd17'),
(155, 'lime lighten-5', '#f9fbe7'),
(156, 'lime lighten-4', '#f0f4c3'),
(157, 'lime lighten-3', '#e6ee9c'),
(158, 'lime lighten-2', '#dce775'),
(159, 'lime lighten-1', '#d4e157'),
(160, 'lime', '#cddc39'),
(161, 'lime darken-1', '#c0ca33'),
(162, 'lime darken-2', '#afb42b'),
(163, 'lime darken-3', '#9e9d24'),
(164, 'lime darken-4', '#827717'),
(165, 'lime accent-1', '#f4ff81'),
(166, 'lime accent-2', '#eeff41'),
(167, 'lime accent-3', '#c6ff00'),
(168, 'lime accent-4', '#aeea00'),
(169, 'yellow lighten-5', '#fffde7'),
(170, 'yellow lighten-4', '#fff9c4'),
(171, 'yellow lighten-3', '#fff59d'),
(172, 'yellow lighten-2', '#fff176'),
(173, 'yellow lighten-1', '#ffee58'),
(174, 'yellow', '#ffeb3b'),
(175, 'yellow darken-1', '#fdd835'),
(176, 'yellow darken-2', '#fbc02d'),
(177, 'yellow darken-3', '#f9a825'),
(178, 'yellow darken-4', '#f57f17'),
(179, 'yellow accent-1', '#ffff8d'),
(180, 'yellow accent-2', '#ffff00'),
(181, 'yellow accent-3', '#ffea00'),
(182, 'yellow accent-4', '#ffd600'),
(183, 'amber lighten-5', '#fff8e1'),
(184, 'amber lighten-4', '#ffecb3'),
(185, 'amber lighten-3', '#ffe082'),
(186, 'amber lighten-2', '#ffd54f'),
(187, 'amber lighten-1', '#ffca28'),
(188, 'amber', '#ffc107'),
(189, 'amber darken-1', '#ffb300'),
(190, 'amber darken-2', '#ffa000'),
(191, 'amber darken-3', '#ff8f00'),
(192, 'amber darken-4', '#ff6f00'),
(193, 'amber accent-1', '#ffe57f'),
(194, 'amber accent-2', '#ffd740'),
(195, 'amber accent-3', '#ffc400'),
(196, 'amber accent-4', '#ffab00'),
(197, 'orange lighten-5', '#fff3e0'),
(198, 'orange lighten-4', '#ffe0b2'),
(199, 'orange lighten-3', '#ffcc80'),
(200, 'orange lighten-2', '#ffb74d'),
(201, 'orange lighten-1', '#ffa726'),
(202, 'orange', '#ff9800'),
(203, 'orange darken-1', '#fb8c00'),
(204, 'orange darken-2', '#f57c00'),
(205, 'orange darken-3', '#ef6c00'),
(206, 'orange darken-4', '#e65100'),
(207, 'orange accent-1', '#ffd180'),
(208, 'orange accent-2', '#ffab40'),
(209, 'orange accent-3', '#ff9100'),
(210, 'orange accent-4', '#ff6d00'),
(211, 'deep-orange lighten-5', '#fbe9e7'),
(212, 'deep-orange lighten-4', '#ffccbc'),
(213, 'deep-orange lighten-3', '#ffab91'),
(214, 'deep-orange lighten-2', '#ff8a65'),
(215, 'deep-orange lighten-1', '#ff7043'),
(216, 'deep-orange', '#ff5722'),
(217, 'deep-orange darken-2', '#f4511e'),
(218, 'deep-orange darken-4', '#e64a19'),
(219, 'deep-orange darken-6', '#d84315'),
(220, 'deep-orange darken-8', '#bf360c'),
(221, 'deep-orange darken-1', '#ff9e80'),
(222, 'deep-orange darken-3', '#ff6e40'),
(223, 'deep-orange darken-5', '#ff3d00'),
(224, 'deep-orange darken-7', '#dd2c00'),
(225, 'brown lighten-5', '#efebe9'),
(226, 'brown lighten-4', '#d7ccc8'),
(227, 'brown lighten-3', '#bcaaa4'),
(228, 'brown lighten-2', '#a1887f'),
(229, 'brown lighten-1', '#8d6e63'),
(230, 'brown', '#795548'),
(231, 'brown darken-1', '#6d4c41'),
(232, 'brown darken-2', '#5d4037'),
(233, 'brown darken-3', '#4e342e'),
(234, 'brown darken-4', '#3e2723'),
(235, 'grey lighten-5', '#fafafa'),
(236, 'grey lighten-4', '#f5f5f5'),
(237, 'grey lighten-3', '#eeeeee'),
(238, 'grey lighten-2', '#e0e0e0'),
(239, 'grey lighten-1', '#bdbdbd'),
(240, 'grey', '#9e9e9e'),
(241, 'grey darken-1', '#757575'),
(242, 'grey darken-2', '#616161'),
(243, 'grey darken-3', '#424242'),
(244, 'grey darken-4', '#212121'),
(245, 'blue-grey lighten-5', '#eceff1'),
(246, 'blue-grey lighten-4', '#cfd8dc'),
(247, 'blue-grey lighten-3', '#b0bec5'),
(248, 'blue-grey lighten-2', '#90a4ae'),
(249, 'blue-grey lighten-1', '#78909c'),
(250, 'blue-grey', '#607d8b'),
(251, 'blue-grey darken-1', '#546e7a'),
(252, 'blue-grey darken-2', '#455a64'),
(253, 'blue-grey darken-3', '#37474f'),
(254, 'blue-grey darken-4', '#263238'),
(255, 'black', '#000000'),
(256, 'white', '#ffffff'),
(257, 'mdb-color lighten-5', '#d0d6e2'),
(258, 'mdb-color lighten-4', '#b1bace'),
(259, 'mdb-color lighten-3', '#929fba'),
(260, 'mdb-color lighten-2', '#7283a7'),
(261, 'mdb-color lighten-1', '#59698d'),
(262, 'mdb-color', '#45526e'),
(263, 'mdb-color darken-1', '#3b465e'),
(264, 'mdb-color darken-2', '#2e3951'),
(265, 'mdb-color darken-3', '#1c2a48'),
(266, 'mdb-color darken-4', '#1c2331');

-- --------------------------------------------------------

--
-- Struttura della tabella `t_locations`
--

CREATE TABLE `t_locations` (
  `id` int(11) NOT NULL,
  `description` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dump dei dati per la tabella `t_locations`
--

INSERT INTO `t_locations` (`id`, `description`) VALUES
(0, 'sconosciuta');

-- --------------------------------------------------------

--
-- Struttura della tabella `t_nodi`
--

CREATE TABLE `t_nodi` (
  `id` int(11) NOT NULL,
  `ip` char(15) NOT NULL,
  `type_id` int(11) NOT NULL,
  `mac` char(17) NOT NULL,
  `location_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `t_options`
--

CREATE TABLE `t_options` (
  `id` int(11) NOT NULL,
  `color_scheme` varchar(30) NOT NULL,
  `min_timestamp` int(11) NOT NULL,
  `max_timestamp` int(11) NOT NULL,
  `plan` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dump dei dati per la tabella `t_options`
--

INSERT INTO `t_options` (`id`, `color_scheme`, `min_timestamp`, `max_timestamp`, `plan`) VALUES
(1, 'indigo darken-4', 0, 0, 'realistic');

-- --------------------------------------------------------

--
-- Struttura della tabella `t_planlabels`
--

CREATE TABLE `t_planlabels` (
  `id` int(11) NOT NULL,
  `eltype` varchar(10) DEFAULT NULL,
  `nodeid` varchar(10) DEFAULT NULL,
  `nodedata` varchar(50) DEFAULT NULL,
  `id_user` varchar(255) DEFAULT NULL,
  `fromtop` varchar(6) DEFAULT NULL,
  `fromleft` varchar(6) DEFAULT NULL,
  `width` varchar(6) DEFAULT NULL,
  `height` varchar(6) DEFAULT NULL,
  `textcontent` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `t_type0_data`
--

CREATE TABLE `t_type0_data` (
  `id` int(11) NOT NULL,
  `node_id` int(11) NOT NULL,
  `tstamp` int(11) NOT NULL,
  `temp` decimal(5,2) NOT NULL,
  `hum` decimal(5,2) NOT NULL,
  `rssi` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `t_type0_options`
--

CREATE TABLE `t_type0_options` (
  `node_id` int(11) NOT NULL,
  `timebetweenread` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `t_types`
--

CREATE TABLE `t_types` (
  `id` int(11) NOT NULL,
  `description` varchar(255) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dump dei dati per la tabella `t_types`
--

INSERT INTO `t_types` (`id`, `description`, `category_id`) VALUES
(0, 'DHT22: temperatura e umidita\'', 0);

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `t_categories`
--
ALTER TABLE `t_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `description` (`description`);

--
-- Indici per le tabelle `t_colors`
--
ALTER TABLE `t_colors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `color_hex` (`color_hex`),
  ADD UNIQUE KEY `color_name` (`color_name`);

--
-- Indici per le tabelle `t_locations`
--
ALTER TABLE `t_locations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `description` (`description`);

--
-- Indici per le tabelle `t_nodi`
--
ALTER TABLE `t_nodi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `mac` (`mac`);

--
-- Indici per le tabelle `t_options`
--
ALTER TABLE `t_options`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `t_planlabels`
--
ALTER TABLE `t_planlabels`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `t_type0_data`
--
ALTER TABLE `t_type0_data`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `t_type0_options`
--
ALTER TABLE `t_type0_options`
  ADD PRIMARY KEY (`node_id`);

--
-- Indici per le tabelle `t_types`
--
ALTER TABLE `t_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `description` (`description`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `t_categories`
--
ALTER TABLE `t_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT per la tabella `t_colors`
--
ALTER TABLE `t_colors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=267;

--
-- AUTO_INCREMENT per la tabella `t_locations`
--
ALTER TABLE `t_locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT per la tabella `t_nodi`
--
ALTER TABLE `t_nodi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT per la tabella `t_options`
--
ALTER TABLE `t_options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT per la tabella `t_planlabels`
--
ALTER TABLE `t_planlabels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT per la tabella `t_type0_data`
--
ALTER TABLE `t_type0_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
