ALTER TABLE `FedMart_users` ADD `buyer_rank` INT( 11 ) NULL DEFAULT '0' AFTER `roles` ;
ALTER TABLE `FedMart_users` ADD `prod_rank` INT( 11 ) NULL DEFAULT '0' AFTER `roles` ;

CREATE TABLE IF NOT EXISTS `FedMart_rank` (
  `rankid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` int(11) NOT NULL,
  `rankname` varchar(255) COLLATE utf8_bin NOT NULL,
  `requirement` int(11) NOT NULL,
  KEY `rankid` (`rankid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=18 ;

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
(10, 2, 'pierre-feuille-ciseaux\r\n', 5),
(11, 2, 'truelle', 20),
(12, 2, 'bricoleur ', 50),
(13, 2, 'technicien', 100),
(14, 2, 'ingénieur', 150),
(15, 2, 'scientifique', 250),
(16, 2, 'inventeur', 350),
(17, 1, 'Jerôme Kerviel', 3000);
