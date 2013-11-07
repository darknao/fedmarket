<?php

define('VERSION', '0.4.8');
define('ICON', IMG_DIR.'/Icons/');
define('ICON_16', IMG_DIR.'/icons/16_16/');
define('ICON_32', IMG_DIR.'/icons/32_32/');
define('LOADER_IMG', '<img src=\"ajax.gif\" />');
define('TPLPATH', 'templates/');

require_once("class.eveDB.php");
require_once("class.utils.php");
require_once("class.eveItem.php");
require_once("class.character.php");
require_once("class.market.php");
require_once("class.order.php");

require_once("class.globalStat.php");
require_once("class.charStat.php");

require_once("class.admin.php");

require_once("graphs.inc.php");
require_once("class.template.php");


?>
