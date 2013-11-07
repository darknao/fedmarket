<?

require_once("config.php");

passthru('cd upgrade;./upgradePrice.sh');

$db = mysql_connect(DB_HOST, DB_USER,DB_PASS) or die('Erreur de connexion '.mysql_error());
mysql_select_db(DB_NAME,$db)  or die('Erreur de selection '.mysql_error());

$allusers = "select `characterID`,`character`,`achievements`, `roles` from `FedMart_users`";
$SQL = "select * from FedMart_achievements";

$reqallusers = mysql_query($allusers) or die('Erreur SQL !<br>'.$allusers.'<br>'.mysql_error());
$req = mysql_query($SQL) or die('Erreur SQL !<br>'.$SQL.'<br>'.mysql_error());

while($allusers = mysql_fetch_object($reqallusers)){
$charID = $allusers->characterID;
while ($achiev = mysql_fetch_object($req)) {
$sql = "select * from FedMart_usersAchievements WHERE userID=$charID and achievID=$achiev->id";
$reqsql = mysql_query($sql) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());
if (mysql_num_rows($reqsql)){echo "$allusers->character :pas";continue;}
eval("\$sql = \"".$achiev->sql."\";");
$reqachiv = mysql_query($sql) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());
if(mysql_num_rows($reqachiv)){
echo "$allusers->character : $achiev->name <br />";
$insert = "insert into FedMart_usersAchievements (userID, achievID) values ($charID, $achiev->id)";
mysql_query($insert) or die('Erreur SQL !<br>'.$insert.'<br>'.mysql_error());
}
//echo $sql;
}
mysql_data_seek($req,0);
}
echo "done";

?>
