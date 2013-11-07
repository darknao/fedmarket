<?php
require_once("../config.php");
  $db = mysql_connect(DB_HOST, DB_USER,DB_PASS) or die('Erreur de connexion '.mysql_error());
  $SQL = "
  SELECT startTime,seller.character as prodeur, sellerc.corpName  as prodeurCorp ,buyer.character as acheteur ,buyerc.corpName as acheteurCorp, (quantity*cost) as total,forCorp, corpOnly, endTime, currentStep, invTypes.typeName, IFNULL(invMetaTypes.metaGroupID,1) as tech  FROM `FedMart_orders` 
INNER JOIN FedMart_users  as buyer ON (FedMart_orders.owner=buyer.characterID)
INNER JOIN FedMart_users  as seller ON (FedMart_orders.assignedTo=seller.characterID)
LEFT JOIN FedMart_corp as buyerc ON (buyer.corpID=buyerc.corpID)
LEFT JOIN FedMart_corp as sellerc ON (seller.corpID=sellerc.corpID)
    LEFT JOIN invTypes ON (invTypes.typeID = FedMart_orders.itemID)
    LEFT JOIN invGroups ON (invGroups.groupID = invTypes.groupID)
    LEFT JOIN invMetaTypes ON (invTypes.typeID = invMetaTypes.typeID)
WHERE 1
ORDER BY startTime DESC";
  mysql_select_db(DB_NAME,$db)  or die('Erreur de selection '.mysql_error());
  $export  = mysql_query($SQL) or die('Erreur SQL !<br>'.$SQL.'<br>'.mysql_error());
  $fields = mysql_num_fields ( $export  );
for ( $i = 0; $i < $fields; $i++ )
{
    $header .= mysql_field_name( $export , $i ) . ",";
}
$header = trim( $header,',' );

while( $row = mysql_fetch_row( $export ) )
{
    $line = '';
    foreach( $row as $value )
    {                                            
        //if ( ( !isset( $value ) ) || ( $value == "" ) )
        //{
        //    $value = "\t";
        //}
        //else
        //{
            $value = str_replace( '"' , '""' , $value );
           // $value = '"' . $value . '"' . ",";
            $value = $value . ",";

        //}
        $line .= $value;
    }
    $data .= trim( $line,',' ) . "\n";
}
$data = str_replace( "\r" , "" , $data );

if ( $data == "" )
{
    $data = "\n(0) Records Found!\n";                        
}

header("Content-type: text/csv");
header("Content-Disposition: attachment; filename=allorders.csv");
header("Pragma: no-cache");
header("Expires: 0");
print "$header\n$data";

?>
