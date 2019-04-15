-- phpMyAdmin SQL Dump
-- version 3.4.10.1deb1
-- http://www.phpmyadmin.net
--
-- Client: localhost
-- Généré le : Jeu 02 Juillet 2015 à 18:04
-- Version du serveur: 5.5.37
-- Version de PHP: 5.3.10-1ubuntu3.18

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données: `vacationpanel`
--
CREATE DATABASE `vacationpanel` DEFAULT CHARACTER SET utf8 COLLATE utf8_bin;
USE `vacationpanel`;

-- --------------------------------------------------------

--
-- Structure de la table `dims_mod_vacationpanel_users`
--

CREATE TABLE IF NOT EXISTS `dims_mod_vacationpanel_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` varchar(64) CHARACTER SET utf8 NOT NULL,
  `password` text COLLATE utf8_bin NOT NULL,
  `role` varchar(23) CHARACTER SET utf8 NOT NULL DEFAULT 'worker',
  `days` int(11) NOT NULL DEFAULT '25',
  `lastname` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT 'X',
  `firstname` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT 'Mr.',
  `email` text CHARACTER SET utf8 NOT NULL,
  `isFirstLogin` tinyint(1) NOT NULL DEFAULT '1',
  `isEnabled` tinyint(1) NOT NULL DEFAULT '1',
  `relog_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `defaultTab` varchar(15) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=18 ;

--
-- Contenu de la table `dims_mod_vacationpanel_users`
--

INSERT INTO `dims_mod_vacationpanel_users` (`id`, `user`, `password`, `role`, `days`, `lastname`, `firstname`, `email`, `isFirstLogin`, `isEnabled`, `relog_time`, `defaultTab`) VALUES
(1, 'francoislefevre', '$2y$10$QBtbSo.XmbSTLtbxINP./u.p8XZN7Ek7aOgGs41LBfvNYBxGi9jOO', 'worker;manager;sysadmin', 15, 'Lef&egrave;vre', 'Fran&ccedil;ois', 'francois.lefevre.email@orange.fr', 1, 1, '2015-07-02 11:34:37', '');

-- --------------------------------------------------------

--
-- Structure de la table `dims_mod_vacationpanel_vacations`
--

CREATE TABLE IF NOT EXISTS `dims_mod_vacationpanel_vacations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `vacationDate` date NOT NULL,
  `comment` text COLLATE utf8_bin NOT NULL,
  `state` varchar(7) CHARACTER SET utf8 NOT NULL,
  `validationDate` text COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
