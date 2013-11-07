<?php
// $Id: register.php 4 2011-01-04 15:08:09Z nao $


require_once("config.php");
$oth_alli = unserialize (OTHER_ALLI);
$oth_corp = unserialize(OTHER_CORP);

$db = mysql_connect(DB_HOST, DB_USER,DB_PASS) or die('Erreur de connexion '.mysql_error());
mysql_select_db(DB_NAME,$db)  or die('Erreur de selection '.mysql_error());

echo "<head>
<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
<title>Federatis Market v",VERSION,"</title>
<link href='css/nao.css' rel='stylesheet' type='text/css'>
<script type=\"text/javascript\">";
if($_SERVER['HTTP_EVE_TRUSTED'] == "No") echo "CCPEVE.requestTrust('http://".$_SERVER['SERVER_NAME']."');setTimeout('window.location.reload()',5000);";
// header("eve.trustme:http://".$_SERVER['SERVER_NAME']."/::You need to trust this site if you want to register.");
echo "</script>


</head>
<body>";
//echo $_SERVER['HTTP_EVE_TRUSTED'];
if($_SERVER['HTTP_EVE_TRUSTED'] == "Yes" && isset($_SERVER['HTTP_EVE_CORPID']) && isset($_SERVER['HTTP_EVE_CHARID'])) {
echo "Trusted !<br />";
$username = $_SERVER['HTTP_EVE_CHARNAME'];
$charID = $_SERVER['HTTP_EVE_CHARID'];
$corpID = $_SERVER['HTTP_EVE_CORPID'];
$corpName = $_SERVER['HTTP_EVE_CORPNAME'];
$alliID = $_SERVER['HTTP_EVE_ALLIANCEID'];


echo "Corp : $corpName <br />";
//if($corpID != MAIN_CORP)die("Only A-M members can register for now !");
if($alliID != MAIN_ALLI && !in_array($alliID, $oth_alli) && !in_array($corpID, $oth_corp) )die("Only A-F members can register for now !");

echo "Welcome $username \o/ <br /><br /> Choose your password : <br />";

echo "<form method=\"post\" action=\"lib/db.php\">
<input type=\"hidden\" name=\"name\" value=\"$username\" />
<input type=\"hidden\" name=\"ID\" value=\"$charID\" />
Password : <input type='password' name='pass' value='' style='width:110px;' /><br />
<input type='hidden' name='corpID' value='$corpID'>
<input type='hidden' name='alliID' value='$alliID'>
<input type='submit' name='register' value='Register' />
</form>";


} 
elseif (!isset($_SERVER['HTTP_EVE_TRUSTED'])) {
echo "Registration work only with the In Game Browser!<br />";

}
else {

echo "You must add this address to your TRUSTED SITES : http://".$_SERVER['SERVER_NAME']."<br />";

}

echo "</body>";
mysql_close();
?>
