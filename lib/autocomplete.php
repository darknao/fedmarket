<?php
/*
$Id: autocomplete.php 11 2011-12-03 16:21:41Z nao $
*/

require_once("../config.php");
$db = mysql_connect(DB_HOST, DB_USER,DB_PASS) or die('Erreur de connexion '.mysql_error());
if (isset($_GET['q'])) {
  $query = mysql_real_escape_string($_GET['q']);
  $itemSQL = "SELECT invTypes.typeID, invTypes.typeName, invTypes.marketGroupID
  FROM invTypes 
  INNER JOIN invBlueprintTypes ON invTypes.typeID=invBlueprintTypes.productTypeID
  LEFT JOIN invMetaTypes as imt ON imt.typeID=invTypes.typeID
  WHERE invTypes.typeName like \"%$query%\"
  AND invTypes.marketGroupID IS NOT NULL
  AND (imt.metaGroupID IS NULL or imt.metaGroupID NOT BETWEEN 3 AND 6 )
  AND getRootMG(invTypes.marketGroupID) NOT IN (2,19,24,150,477,1320,1396, 1659)
  ORDER BY invTypes.typeName";

                                                                      
  mysql_select_db(DB_NAME,$db)  or die('Erreur de selection '.mysql_error());
  $req = mysql_query($itemSQL) or die('Erreur SQL !<br>'.$itemSQL.'<br>'.mysql_error());
  while ($item = mysql_fetch_object($req)){
   $ico = IMG_DIR."/Types/".$item->typeID."_32.png";
  echo "$item->typeName|$item->typeID|$ico\r\n";
  
  
  }
}
mysql_close();
?>
