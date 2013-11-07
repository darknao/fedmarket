<?php
// $Id: index.php 11 2011-12-03 16:21:41Z nao $


//if (!isset($_GET['debugmode'])) die("Migration en cours...");

require_once("config.php");
session_start();

if ($_SESSION["logged_in"] && $_SESSION["userName"]){
  $user = $_SESSION["userName"];
  $logged_in = $_SESSION["logged_in"];
  $userID = $_SESSION["userID"];
  $role = $_SESSION["role"];
}
if($_SERVER['HTTP_EVE_TRUSTED'] == "Yes") $inEVE = true;

$market = new market();

if($inEVE) $IGBMode = "<b>IGBMode</b> -";

if($logged_in){
  $loginForm = "<div id='login'>Logged as $user (<a href=\"#\" onclick=\"javascript:Logout();\">logout</a>)</div>";
  $userMenu = "<div id='userMenu'><a href=\"#!/home\">Home</a> - <a href=\"#!/market\">Market</a> - <a href=\"#!/tools\">Tools</a>";
  if($role >= 2)$userMenu .= " - <a href=\"#!/PL\">Prod Line</a>";
  $userMenu .= " - <a href=\"#!/freight\">Freight</a> - <a href=\"#!/myOrders\">My Orders</a> - <a href=\"#!/stats\">Stats</a>";
  if($role >= 4)$userMenu .= " - <a href=\"#!/Admin\">Admin</a>";
  $userMenu .= " - <a id=\"chatToggle\" href=\"#\" onclick=\"javascript:toggleChat();\"><span class=\"ui-icon ui-icon-comment\"></span></a></div>";
}
else {
  $loginForm = "<div id='login'>$IGBMode <a href=\"#\" onclick=\"javascript:showLogin();\">Login</a></div>";
  $userMenu = "<div id='userMenu'><a href=\"#!/home\">Home</a> - <a href=\"#!/market\">Market</a> - <a href=\"#!/tools\">Tools</a> - <a href=\"#!/stats\">Stats</a></div>";
}

$index = new Template(TPLPATH."index.tpl", array(
  'usermenu' => $userMenu,
  'loginForm' => $loginForm,
  'market' => $market->loadCategory(),
  'IGBMode' => $IGBMode,
  'loggedin' => $logged_in,
));

$index->render();

?>
