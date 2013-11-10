CREATE TABLE IF NOT EXISTS `FedMart_achievements` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `desc` text NOT NULL,
  `sql` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=8 ;

INSERT INTO `FedMart_achievements` (`id`, `name`, `desc`, `sql`) VALUES
(1, 'Chips Master', 'Produire plus de 10 Cap Ships', 'SELECT sum(quantity) as total\r\n    FROM `FedMart_orders`\r\n    inner JOIN invTypes ON itemID=invTypes.typeID\r\n    inner JOIN invMarketGroups ON invTypes.marketGroupID=invMarketGroups.marketGroupID\r\n    WHERE assignedTo = $charID\r\n    AND invMarketGroups.parentGroupID IN (761,766,812,817,1047,1089)\r\n    AND currentStep = 10\r\n    group by assignedTo\r\nhaving total > 10'),
(2, 'Tech I specialist', 'Produire au moins 2000 items TI, sans compter les munitions', ' SELECT sum(quantity) as total\r\n    FROM `FedMart_orders`\r\n    inner JOIN invTypes ON itemID=invTypes.typeID\r\n    inner JOIN invBlueprintTypes ON itemID=invBlueprintTypes.productTypeID\r\n    inner JOIN invGroups ON invTypes.groupID=invGroups.groupID\r\n    WHERE currentStep = ''10'' AND (assignedTo = $charID)\r\n    AND categoryID != 8\r\nand techlevel=1\r\n    group by assignedTo\r\nhaving total >= 2000'),
(3, 'Tech II specialist', 'Vendre plus de 1000 item TII', ' SELECT sum(quantity) as total\r\n    FROM `FedMart_orders`\r\n    inner JOIN invTypes ON itemID=invTypes.typeID\r\n    inner JOIN invBlueprintTypes ON itemID=invBlueprintTypes.productTypeID\r\n    inner JOIN invGroups ON invTypes.groupID=invGroups.groupID\r\n    WHERE currentStep = ''10'' AND (assignedTo = $charID)\r\n    AND categoryID != 8\r\nand techlevel=2\r\n    group by assignedTo\r\nhaving total > 1000'),
(4, 'Tech III specialist', 'Vendre plus de 30 ships TIII', ' SELECT sum(quantity) as total\r\n    FROM `FedMart_orders`\r\n    inner JOIN invTypes ON itemID=invTypes.typeID\r\n    inner JOIN invBlueprintTypes ON itemID=invBlueprintTypes.productTypeID\r\n    inner JOIN invGroups ON invTypes.groupID=invGroups.groupID\r\n    WHERE currentStep = ''10'' AND (assignedTo = $charID)\r\n    AND categoryID != 8\r\nand techlevel=3\r\n    group by assignedTo\r\nhaving total > 30'),
(5, 'Des chips ! On va manger des CHIPS !!', 'Acheter au moins 3 capital ship', 'SELECT sum(quantity) as total\r\n    FROM `FedMart_orders`\r\n    inner JOIN invTypes ON itemID=invTypes.typeID\r\n    inner JOIN invMarketGroups ON invTypes.marketGroupID=invMarketGroups.marketGroupID\r\n    WHERE owner= $charID\r\n    AND invMarketGroups.parentGroupID IN (761,766,812,817,1047,1089)\r\n    and currentStep=10\r\n   group by owner\r\nhaving total > 3'),
(6, 'Acheteur compulsif', 'Avoir passé plus de 100 commandes', 'SELECT count(distinct MorderID) as total\r\n    FROM `FedMart_orders`\r\n    inner JOIN invTypes ON itemID=invTypes.typeID\r\n    inner JOIN invMarketGroups ON invTypes.marketGroupID=invMarketGroups.marketGroupID\r\n    WHERE owner= $charID\r\n    AND MorderID > 0\r\n    and currentStep=10\r\n   having total > 100'),
(7, 'De Rothschild', 'Dépenser plus de 3B sur le market', 'SELECT sum(quantity*cost) as total\r\n    FROM `FedMart_orders`\r\n    inner JOIN invTypes ON itemID=invTypes.typeID\r\n    inner JOIN invMarketGroups ON invTypes.marketGroupID=invMarketGroups.marketGroupID\r\n    WHERE owner= $charID\r\n    and currentStep=10\r\n    having total > 3000000000');


CREATE TABLE IF NOT EXISTS `FedMart_usersAchievements` (
  `id` int(11) NOT NULL auto_increment,
  `userID` int(11) NOT NULL,
  `achievID` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=8 ;


