-- phpMyAdmin SQL Dump
-- version 3.4.11.1deb2
-- http://www.phpmyadmin.net
--
-- Client: localhost
-- Généré le: Dim 10 Novembre 2013 à 14:36
-- Version du serveur: 5.5.31
-- Version de PHP: 5.4.4-14+deb7u4

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données: `market`
--

DELIMITER $$
--
-- Fonctions
--
CREATE FUNCTION `getRootMG`(groupID INT(10)) RETURNS int(10)
BEGIN
   DECLARE parent INT;
   REPEAT
       select parentGroupID INTO parent from invMarketGroups where marketGroupID=groupID LIMIT 1;
       IF parent IS NOT NULL THEN SET groupID = parent; END IF;
   UNTIL parent IS NULL
   END REPEAT;
   RETURN groupID;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `apicache`
--

CREATE TABLE IF NOT EXISTS `apicache` (
  `host` varchar(64) NOT NULL,
  `path` varchar(64) NOT NULL,
  `params` varchar(64) NOT NULL,
  `content` longtext NOT NULL,
  `cachedUntil` datetime DEFAULT NULL,
  PRIMARY KEY (`host`,`path`,`params`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `FedMart_achievements`
--

CREATE TABLE IF NOT EXISTS `FedMart_achievements` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `desc` text NOT NULL,
  `sql` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

--
-- Contenu de la table `FedMart_achievements`
--

INSERT INTO `FedMart_achievements` (`id`, `name`, `desc`, `sql`) VALUES
(1, 'Chips Master', 'Produire plus de 10 Cap Ships', 'SELECT sum(quantity) as total\r\n    FROM `FedMart_orders`\r\n    inner JOIN invTypes ON itemID=invTypes.typeID\r\n    inner JOIN invMarketGroups ON invTypes.marketGroupID=invMarketGroups.marketGroupID\r\n    WHERE assignedTo = $charID\r\n    AND invMarketGroups.parentGroupID IN (761,766,812,817,1047,1089)\r\n    AND currentStep = 10\r\n    group by assignedTo\r\nhaving total > 10'),
(2, 'Tech I specialist', 'Produire au moins 2000 items TI, sans compter les munitions', ' SELECT sum(quantity) as total\r\n    FROM `FedMart_orders`\r\n    inner JOIN invTypes ON itemID=invTypes.typeID\r\n    inner JOIN invBlueprintTypes ON itemID=invBlueprintTypes.productTypeID\r\n    inner JOIN invGroups ON invTypes.groupID=invGroups.groupID\r\n    WHERE currentStep = ''10'' AND (assignedTo = $charID)\r\n    AND categoryID != 8\r\nand techlevel=1\r\n    group by assignedTo\r\nhaving total >= 2000'),
(3, 'Tech II specialist', 'Vendre plus de 1000 item TII', ' SELECT sum(quantity) as total\r\n    FROM `FedMart_orders`\r\n    inner JOIN invTypes ON itemID=invTypes.typeID\r\n    inner JOIN invBlueprintTypes ON itemID=invBlueprintTypes.productTypeID\r\n    inner JOIN invGroups ON invTypes.groupID=invGroups.groupID\r\n    WHERE currentStep = ''10'' AND (assignedTo = $charID)\r\n    AND categoryID != 8\r\nand techlevel=2\r\n    group by assignedTo\r\nhaving total > 1000'),
(4, 'Tech III specialist', 'Vendre plus de 30 ships TIII', ' SELECT sum(quantity) as total\r\n    FROM `FedMart_orders`\r\n    inner JOIN invTypes ON itemID=invTypes.typeID\r\n    inner JOIN invBlueprintTypes ON itemID=invBlueprintTypes.productTypeID\r\n    inner JOIN invGroups ON invTypes.groupID=invGroups.groupID\r\n    WHERE currentStep = ''10'' AND (assignedTo = $charID)\r\n    AND categoryID != 8\r\nand techlevel=3\r\n    group by assignedTo\r\nhaving total > 30'),
(5, 'Des chips ! On va manger des CHIPS !!', 'Acheter au moins 3 capital ship', 'SELECT sum(quantity) as total\r\n    FROM `FedMart_orders`\r\n    inner JOIN invTypes ON itemID=invTypes.typeID\r\n    inner JOIN invMarketGroups ON invTypes.marketGroupID=invMarketGroups.marketGroupID\r\n    WHERE owner= $charID\r\n    AND invMarketGroups.parentGroupID IN (761,766,812,817,1047,1089)\r\n    and currentStep=10\r\n   group by owner\r\nhaving total > 3'),
(6, 'Acheteur compulsif', 'Avoir passé plus de 100 commandes', 'SELECT count(distinct MorderID) as total\r\n    FROM `FedMart_orders`\r\n    inner JOIN invTypes ON itemID=invTypes.typeID\r\n    inner JOIN invMarketGroups ON invTypes.marketGroupID=invMarketGroups.marketGroupID\r\n    WHERE owner= $charID\r\n    AND MorderID > 0\r\n    and currentStep=10\r\n   having total > 100'),
(7, 'De Rothschild', 'Dépenser plus de 3B sur le market', 'SELECT sum(quantity*cost) as total\r\n    FROM `FedMart_orders`\r\n    inner JOIN invTypes ON itemID=invTypes.typeID\r\n    inner JOIN invMarketGroups ON invTypes.marketGroupID=invMarketGroups.marketGroupID\r\n    WHERE owner= $charID\r\n    and currentStep=10\r\n    having total > 3000000000');


-- --------------------------------------------------------

--
-- Structure de la table `FedMart_alli`
--

CREATE TABLE IF NOT EXISTS `FedMart_alli` (
  `alliID` int(10) unsigned NOT NULL,
  `alliName` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `short` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`alliID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `FedMart_bpAM`
--

CREATE TABLE IF NOT EXISTS `FedMart_bpAM` (
  `typeID` int(11) NOT NULL,
  `groupID` int(11) NOT NULL DEFAULT '0',
  `typeName` varchar(100) DEFAULT NULL,
  `graphicID` int(11) DEFAULT NULL,
  `marketGroupID` int(11) DEFAULT NULL,
  PRIMARY KEY (`typeID`),
  KEY `graphicID` (`graphicID`),
  KEY `marketGroupID` (`marketGroupID`),
  KEY `typeID` (`groupID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `FedMart_corp`
--

CREATE TABLE IF NOT EXISTS `FedMart_corp` (
  `corpID` int(10) unsigned NOT NULL,
  `alliID` int(10) unsigned NOT NULL,
  `corpName` varchar(255) NOT NULL,
  `corpTick` varchar(255) NOT NULL,
  KEY `corpID` (`corpID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `FedMart_matosAM`
--

CREATE TABLE IF NOT EXISTS `FedMart_matosAM` (
  `typeID` int(11) NOT NULL,
  `quantity` int(11) DEFAULT NULL,
  `cost` double DEFAULT NULL,
  `contractPrice` double NOT NULL,
  `SLprice` double NOT NULL DEFAULT '0',
  `market` double unsigned DEFAULT '0',
  `supplyType` tinyint(3) unsigned DEFAULT NULL,
  `lowLimit` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`typeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `FedMart_Morders`
--

CREATE TABLE IF NOT EXISTS `FedMart_Morders` (
  `MorderID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `owner` int(10) NOT NULL,
  `forCorp` tinyint(1) DEFAULT '0',
  `corpOnly` tinyint(1) DEFAULT '0',
  `startTime` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `endTime` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `currentStep` smallint(6) DEFAULT '0',
  PRIMARY KEY (`MorderID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `FedMart_orders`
--

CREATE TABLE IF NOT EXISTS `FedMart_orders` (
  `orderID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `MorderID` int(11) NOT NULL DEFAULT '0',
  `owner` int(10) NOT NULL,
  `forCorp` tinyint(1) DEFAULT '0',
  `corpOnly` tinyint(1) DEFAULT '0',
  `itemID` int(11) DEFAULT NULL,
  `quantity` int(10) DEFAULT NULL,
  `cost` double DEFAULT NULL,
  `startTime` timestamp NULL DEFAULT NULL,
  `endTime` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `jobID` int(11) DEFAULT NULL,
  `currentStep` smallint(6) DEFAULT '0',
  `assignedTo` int(10) DEFAULT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`orderID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `FedMart_prodRight`
--

CREATE TABLE IF NOT EXISTS `FedMart_prodRight` (
  `characterID` int(10) unsigned NOT NULL,
  `TI_mod` tinyint(1) DEFAULT '0',
  `TII_mod` tinyint(1) DEFAULT '0',
  `TI_rig` tinyint(1) DEFAULT '0',
  `TII_rig` tinyint(1) DEFAULT '0',
  `TI_ship` tinyint(1) DEFAULT '0',
  `TII_ship` tinyint(1) DEFAULT '0',
  `TIII_ship` tinyint(1) DEFAULT '0',
  `TI_cap` tinyint(1) DEFAULT '0',
  `TII_cap` tinyint(1) DEFAULT '0',
  `compo` tinyint(1) DEFAULT '0',
  `rightsBit` int(11) DEFAULT '0',
  PRIMARY KEY (`characterID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `FedMart_rank`
--

CREATE TABLE IF NOT EXISTS `FedMart_rank` (
  `rankid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` int(11) NOT NULL,
  `rankname` varchar(255) COLLATE utf8_bin NOT NULL,
  `requirement` int(11) NOT NULL,
  KEY `rankid` (`rankid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Contenu de la table `FedMart_rank`
--

INSERT INTO `FedMart_rank` (`rankid`, `type`, `rankname`, `requirement`) VALUES
(1, 1, 'sale pauvre', 1),
(2, 1, 'radin', 100),
(3, 1, 'pingre', 250),
(4, 1, 'économe', 500),
(5, 1, 'mac', 750),
(6, 1, 'suisse', 1000),
(7, 1, 'bourgeois', 1500),
(8, 1, 'voleur', 2000),
(9, 2, 'pot de colle', 1),
(10, 2, 'pierre-feuille-ciseaux', 5),
(11, 2, 'truelle', 20),
(12, 2, 'bricoleur ', 50),
(13, 2, 'technicien', 100),
(14, 2, 'ingénieur', 150),
(15, 2, 'scientifique', 250),
(16, 2, 'inventeur', 350),
(17, 1, 'Jerôme Kerviel', 3000);

-- --------------------------------------------------------

--
-- Structure de la table `FedMart_roles`
--

CREATE TABLE IF NOT EXISTS `FedMart_roles` (
  `roleID` int(11) NOT NULL,
  `roleName` varchar(255) NOT NULL,
  KEY `roleID` (`roleID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Contenu de la table `FedMart_roles`
--

INSERT INTO `FedMart_roles` (`roleID`, `roleName`) VALUES
(0, 'Client'),
(2, 'Builder'),
(5, 'Administrator'),
(4, 'Rights Manager');

-- --------------------------------------------------------

--
-- Structure de la table `FedMart_users`
--

CREATE TABLE IF NOT EXISTS `FedMart_users` (
  `characterID` int(10) unsigned NOT NULL,
  `character` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `corpID` int(10) unsigned NOT NULL,
  `alliID` int(10) unsigned NOT NULL,
  `roles` int(10) unsigned NOT NULL,
  `achievements` text NOT NULL,
  `prod_rank` int(11) DEFAULT '0',
  `buyer_rank` int(11) DEFAULT '0',
  `sid` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`characterID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `FedMart_usersAchievements`
--

CREATE TABLE IF NOT EXISTS `FedMart_usersAchievements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userID` int(11) NOT NULL,
  `achievID` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;


--
-- Contraintes pour la table `FedMart_prodRight`
--
ALTER TABLE `FedMart_prodRight`
  ADD CONSTRAINT `FedMart_prodRight_ibfk_1` FOREIGN KEY (`characterID`) REFERENCES `FedMart_users` (`characterID`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
