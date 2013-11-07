<?php
// $Id: db.php 22 2012-09-26 13:27:40Z nao $


session_start();
require_once("../config.php");
require_once 'eveapi/factory.php';
require_once 'evelogo.php';

    
$inEVE = false;
if(isset($_SERVER['HTTP_EVE_TRUSTED']))
  if($_SERVER['HTTP_EVE_TRUSTED'] == "Yes")$inEVE = true;
     
    
function logToFile($data){
$Date = date("ymd");
$LogFile = "../log/".$Date."_fedmart.log";
  $CR = 1;
  $logfile = fopen($LogFile, 'a');
  if ($logfile) {
    $finalstring = date("G:i") . " - " . $data . "\n";
    $CR=0;
    if(!fwrite($logfile, $finalstring))$CR=1;
  }
  return $CR;
}

function get_portrait($charID, $size = 256) {
  $base_url= "http://image.eveonline.com/Character";
  $cached_file = "cache/portrait/".$charID."_".$size.".jpg";
  if (!file_exists($cached_file)){
    //$size = "256";
    $param = $charID."_".$size.".jpg";
    $portrait = imagecreatefromjpeg($base_url."/".$param);
    imagejpeg($portrait,$cached_file);
    imagedestroy($portrait);
  }
  return "lib/".$cached_file;
}

function get_logo($corpID, $size = 128) {
  $filename = 'cache/corps/'.$corpID.'_'.$size.'.jpg';

  if (!file_exists($filename)) {
    $eAPI = AleFactory::getEVEOnline();
    $eAPI->setCredentials(API_KEY, API_VCODE);
    $eAPI->setCharacterID(API_CHAR);
       
    $param = array (corporationID => $corpID);
    $corp = $eAPI->corp->CorporationSheet($param);
    $logo = $corp->result->logo;
  
    $data["shape1"] = $logo->shape1;
    $data["shape2"] = $logo->shape2;
    $data["shape3"] = $logo->shape3;
    $data["colour1"] = $logo->color1;
    $data["colour2"] = $logo->color2;
    $data["colour3"] = $logo->color3;
  
    CorporationLogo($data, $size, $filename);
  }
  return "lib/".$filename;
}


function time_duration($seconds, $use = null, $zeros = false)
{
    // Define time periods
    $periods = array (
        'years'     => 31556926,
        'Months'    => 2629743,
        'weeks'     => 604800,
        'days'      => 86400,
        'hours'     => 3600,
        'minutes'   => 60,
        'seconds'   => 1
        );

    // Break into periods
    $seconds = (float) $seconds;
    foreach ($periods as $period => $value) {
        if ($use && strpos($use, $period[0]) === false) {
            continue;
        }
        $count = floor($seconds / $value);
        if ($count == 0 && !$zeros) {
            continue;
        }
        $segments[strtolower($period)] = $count;
        $seconds = $seconds % $value;
    }

    // Build the string
    foreach ($segments as $key => $value) {
        $segment_name = substr($key, 0, -1);
        $segment = $value . ' ' . $segment_name;
        if ($value != 1) {
            $segment .= 's';
        }
        $array[] = $segment;
    }

    $str = implode(', ', $array);
    return $str;
}
if( !function_exists('ceiling') )
{
    function ceiling($number, $significance = 1)
    {
        return ( is_numeric($number) && is_numeric($significance) ) ? (ceil($number/$significance)*$significance) : false;
    }
}

if( !function_exists('flooring') )
{
    function flooring($number, $significance = 1)
    {
        return ( is_numeric($number) && is_numeric($significance) ) ? (floor($number/$significance)*$significance) : false;
    }
}

function speround($number,$precision = 1)
{
  return ( is_numeric($number) && is_numeric($precision) ) ? round((pow(10,log10($number)-(int)(log10($number)))),$precision) * pow(10,(int)(log10($number))) : false;
}



$db = mysql_connect(DB_HOST, DB_USER,DB_PASS) or die('Erreur de connexion '.mysql_error());


if (isset($_POST['type'])) {
  $market = new market();
  $market->corp = $_SESSION["corp"];
  $type = $_POST['type'];
  switch($type) {
    case "category":
        if (!isset($_POST['parentID'])) die("erreur");
        $parentID = $_POST['parentID'];
        
        echo $market->loadCategory($parentID);

        break;
        
    case "item":
      if (isset($_POST['itemID'])) $item = $market->loadItem($_POST['itemID']);
      if (isset($_POST['name'])) $item = $market->loadItem($_POST['name']);
     
      $pcost = number_format($item['cost'], 2, '.', ','). " ISK";         // Your Price
      $pcostSell = number_format($item['costSell'], 2, '.', ','). " ISK"; // Sinq Laison
      $preduc = number_format($item['reduc'], 1, '.', ',')."%";

      if($item['costSell'] ==0)$pcostSell = "n/a";
      if($item['cost'] == 0){$pcost = "n/a";$preduc="";}

      if(isset($_SESSION["ses_basket_forCorp"])){
        if($_SESSION["ses_basket_forCorp"])$chkCorp = "CHECKED DISABLED";
        else $chkCorp = "DISABLED";
      }
      if(isset($_SESSION["ses_basket_corpOnly"])){
        if($_SESSION["ses_basket_corpOnly"])$chkCorpOnly = "CHECKED DISABLED";
        else $chkCorpOnly = "DISABLED";
      }

      $buyitemtpl = new Template(TPLPATH."buyitem.tpl", array(
              'id' => $item['id'],
              'name' => $item['name'],
              'desc' => $item['desc'],
              'sicon' => $item['sicon'],
              'pcost' => $pcost,
              'preduc' => $preduc,
              'typereduc' => $item['typereduc'],
              'pcostSell' => $pcostSell,
              'cost' => $item['cost'],
              'chkCorp' => $chkCorp,
              'chkCorpOnly' => $chkCorpOnly,
              'costSell' => $item['costSell'],
      ));

      $itemtpl = new Template(TPLPATH."item.tpl", array(
              'id' => $item['id'],
              'name' => $item['name'],
              'desc' => $item['desc'],
              'icon' => $item['icon'],
              'showinfoLink' => $showinfoLink,
              'loggedin' => $_SESSION["logged_in"],
              'buyItem' => $buyitemtpl,
      ));

      $itemtpl->render();

      break;
  }
}

if (isset($_POST['getCharInfo'])) {
  $char = new character($_POST['getCharInfo']);
  $char->getSumInfoXML();
}

if (isset($_POST['getMyInfo'])) {
  $char = new character($_SESSION["userID"]);
  $char->getFullInfoXML();

}


if (isset($_POST['login'])) {
//print_r($_COOKIE);
  if(isset($_POST['auto']) && isset($_COOKIE["FedMarket_login"]) && isset($_COOKIE["FedMarket_SID"])) {
    $userID = mysql_real_escape_string($_COOKIE["FedMarket_login"]);
    $lastsid = mysql_real_escape_string($_COOKIE["FedMarket_SID"]);
    
    $SQL = "SELECT * FROM FedMart_users WHERE `characterID` = '$userID' AND `sid` = '$lastsid'";

  }
  elseif(isset($_POST['pass'])) {
    $username = mysql_real_escape_string($_POST['login']);
    $pass = mysql_real_escape_string($_POST['pass']);
    $remember = mysql_real_escape_string($_POST['remember']);
    $SQL = "SELECT * FROM FedMart_users WHERE `character` = '$username' AND `password` = '$pass'";
  }
  else die("KO!");
  
  mysql_select_db(DB_NAME,$db)  or die('Erreur de selection '.mysql_error());
  $req = mysql_query($SQL) or die('Erreur SQL !<br>'.$SQL.'<br>'.mysql_error());
  $result = mysql_fetch_object($req);
  
  if(is_object($result)){
    // Check AlliID
    $oth_alli = unserialize (OTHER_ALLI);
    $oth_corp = unserialize(OTHER_CORP);
    
    if($result->alliID == MAIN_ALLI || in_array($result->alliID, $oth_alli) || in_array($result->corpID, $oth_corp) ) {
      $_SESSION["userName"] = $result->character;
      $_SESSION["userID"] = $result->characterID;
      $_SESSION["logged_in"] = true;
      $_SESSION["role"] = $result->roles;
      $_SESSION["corp"] = $result->corpID;
      $_SESSION["alli"] = $result->alliID;

      $SQL = "SELECT * from FedMart_prodRight WHERE characterID = '$result->characterID' LIMIT 1";
      $req = mysql_query($SQL) or die('Erreur SQL !<br>'.$SQL.'<br>'.mysql_error());
      $prodright = mysql_fetch_object($req);
      if(is_object($prodright)) $_SESSION["prodRight"] = $prodright->rightsBit;

      
      if($remember != "false" || isset($_POST['auto'])){
        setcookie("FedMarket_login",$result->characterID,time()+(3600*24*3), '/');
        setcookie("FedMarket_SID",session_id(),time()+(3600*24*3), '/');
        $SQL = "UPDATE `FedMart_users` SET `sid` =  '".session_id()."' WHERE `FedMart_users`.`characterID` = '$result->characterID'";
        $req = mysql_query($SQL) or die('Erreur SQL !<br>'.$SQL.'<br>'.mysql_error());
      }

      
      echo "
      <character>
        <id>$result->characterID</id>
        <name>$result->character</name>
        <role>$result->roles</role>
        <portrait>".get_portrait($result->characterID)."</portrait>
        <corpName>$result->corpID</corpName>
        <alliName>$result->corpID</alliName>
      </character>
      ";
      logToFile("Login successfull : [$result->corpID]$result->character ($result->roles)");
      //echo "Welcome back $username !";
    } else {
      logToFile("Login Denied! for $username : wrong alliID: $result->alliID");
      echo "KO";
    }
  }
  else {
    logToFile("Login Denied! for $username");
    echo "KO";
  }

}

if (isset($_POST['logout'])) {

  $_SESSION = array();
  if (isset($_COOKIE[session_name()])) {
      setcookie(session_name(), '', time()-42000, '/');
  }
  setcookie("FedMarket_login","",time()-42000, '/');
  setcookie("FedMarket_SID","",time()-42000, '/');
         
  session_destroy();
}

if (isset($_POST['register'])) {
  echo "<head>
  <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
  <title>Federatis Market v0.1 alpha</title>
  </head>
  <body>";
  $username = mysql_real_escape_string($_POST['name']);
  $pass = sha1($_POST['pass']);
  $charID = $_POST['ID'];
  $corpID = $_POST['corpID'];
  $alliID = $_POST['alliID'];
  
  $SQL = "SELECT * FROM FedMart_users WHERE `characterID` = '$charID' LIMIT 1";
  mysql_select_db(DB_NAME,$db)  or die('Erreur de selection '.mysql_error());
  $req = mysql_query($SQL) or die('Erreur SQL !<br>'.$SQL.'<br>'.mysql_error());
  $result = mysql_fetch_object($req);
  if(is_object($result)) $SQL = "UPDATE FedMart_users SET `password`='$pass' WHERE `characterID` = '$charID'";
  else $SQL = "INSERT INTO FedMart_users (`characterID`,`character`,`password`,`corpID`,`alliID`) VALUES('$charID','$username','$pass','$corpID','$alliID')";
  //echo "dbug : $SQL<br />";
  $req = mysql_query($SQL) or die('Erreur SQL !<br>'.$SQL.'<br>'.mysql_error());
  
  
  echo "$username registered !<br /> You can go [<a href='../'>here</a>] now !";

echo "</body>";
}
/*
if (isset($_POST['order'])) {
  if ($_SESSION["logged_in"]){
  $userID = $_SESSION["userID"];
  $logged_in = $_SESSION["logged_in"];
  $itemID = $_POST['order'];
  $qty = $_POST['qty'];
  $cost = $_POST['cost'];
  $forCorp = ((bool)$_POST['forCorp'] ? 1 : 0) ;
  
 // $datenow = $_SERVER['REQUEST_TIME'];
  
  $orderSQL = "INSERT INTO FedMart_orders (`owner`,`forCorp`,`itemID`,`quantity`,`cost`)
                VALUES('$userID','$forCorp', '$itemID', '$qty', '$cost')";
  mysql_select_db(DB_NAME,$db)  or die('Erreur de selection '.mysql_error());
  $req = mysql_query($orderSQL) or die('Order Failed !!');


  echo "Order successfull";
 
    
 } 
}
*/
if (isset($_POST['basket'])) {
  switch ($_POST['basket']){
    case 'add':
      $itemID = $_POST['itemID'];
      $qty = $_POST['qty'];
      $cost = $_POST['cost'];
      $itemName = $_POST['itemName'];
      $forCorp = ((bool)$_POST['forCorp'] ? 1 : 0) ;
      $corpOnly = ((bool)$_POST['corpOnly'] ? 1 : 0) ;
      
      if (isset($_SESSION["ses_basket_items"])){
        $basket_position_counter=0;
        $double=0;
        if ($_SESSION["ses_basket_items"]>0){
           foreach ($_SESSION["ses_basket_id"] as $basket_item){
              if ($basket_item==$itemID){
                 $double=1;
                 $basket_position=$basket_position_counter;
              }
              $basket_position_counter++;
           }
        }
        if ($double==1){
           //$oldamount=$_SESSION["ses_basket_amount"][$basket_position];
           $_SESSION["ses_basket_amount"][$basket_position]+=$qty;
           //$amount=$_SESSION["ses_basket_amount"][$basket_position];
           //$oldprice=$_SESSION["ses_basket_price"][$basket_position];
           //$newprice=($oldprice/$oldamount)*$amount;
           //$_SESSION["ses_basket_price"][$basket_position]=$newprice;
        }else{
           $_SESSION["ses_basket_name"][]=$itemName;
           $_SESSION["ses_basket_amount"][]=$qty;
           $_SESSION["ses_basket_price"][]=$cost;
           $_SESSION["ses_basket_id"][]=$itemID;
           $_SESSION["ses_basket_items"]++;
        }
     }else{
        $ses_basket_items=1;
        $ses_basket_name[0]=$itemName;
        $ses_basket_amount[0]=$qty;
        $ses_basket_price[0]=$cost;
        $ses_basket_id[0]=$itemID;
        $_SESSION["ses_basket_items"] = $ses_basket_items;
        $_SESSION["ses_basket_name"] = $ses_basket_name;
        $_SESSION["ses_basket_amount"] = $ses_basket_amount;
        $_SESSION["ses_basket_price"] = $ses_basket_price;
        $_SESSION["ses_basket_id"] = $ses_basket_id;
        $_SESSION["ses_basket_forCorp"] = $forCorp;
        $_SESSION["ses_basket_corpOnly"] = $corpOnly;
    
     }
      echo "Item added!";
      break;
      
    case 'remove':
      $itemID = $_POST['itemID'];
      if ($_SESSION["ses_basket_items"]>0){
        $newcount = 0;
         for ($basket_counter=0;$basket_counter<$_SESSION["ses_basket_items"];$basket_counter++){
            if($_SESSION["ses_basket_id"][$basket_counter] == $itemID)continue;
            $ses_basket_items = $newcount + 1;
            $ses_basket_name[$newcount] = $_SESSION["ses_basket_name"][$basket_counter];
            $ses_basket_amount[$newcount] = $_SESSION["ses_basket_amount"][$basket_counter];
            $ses_basket_price[$newcount] = $_SESSION["ses_basket_price"][$basket_counter];
            $ses_basket_id[$newcount] = $_SESSION["ses_basket_id"][$basket_counter];
            $newcount++;
         }
        $_SESSION["ses_basket_items"] = $ses_basket_items;
        $_SESSION["ses_basket_name"] = $ses_basket_name;
        $_SESSION["ses_basket_amount"] = $ses_basket_amount;
        $_SESSION["ses_basket_price"] = $ses_basket_price;
        $_SESSION["ses_basket_id"] = $ses_basket_id;
        
        echo "Item removed!"; 
      }  
      break;
       
    case 'list':
      if ($_SESSION["ses_basket_items"]>0){
      $total = 0;
      echo "<table class='basketlist'>
              <tr>
                <th></th>
                <th>qty</th>
                <th>item</th>
                <th>price</th>
              </tr>";
         for ($basket_counter=0;$basket_counter<$_SESSION["ses_basket_items"];$basket_counter++){
            $price = $_SESSION["ses_basket_price"][$basket_counter] * $_SESSION["ses_basket_amount"][$basket_counter];
            $total+= $price;
            $price=number_format($price, 2, '.', ',')." ISK";
            $amount=$_SESSION["ses_basket_amount"][$basket_counter];
            $name=$_SESSION["ses_basket_name"][$basket_counter];
            $id=$_SESSION["ses_basket_id"][$basket_counter];

            echo "<tr id='item_$id'><td><a href='#' onclick=\"javascript:basketRemove($id);\"><span class='ui-icon ui-icon-close'></span></a></td><td align='right'>$amount</td><td>$name</td><td align='right'>$price</td></tr>";
         }
         $total=number_format($total, 2, '.', ',')." ISK";
         if($_SESSION["ses_basket_forCorp"])$forCorp = "This is for your Corporation";
         if($_SESSION["ses_basket_corpOnly"])$corpOnly = "Visible only by your Corporation";
         echo "<tr id='total'>
                <td colspan=3>Total</td><td align='right' id='basketTotal'>$total</td>
               </tr>
               <tr>
                <td colspan=4>$forCorp</td>
               </tr>
               <tr>
                <td colspan=4>$corpOnly</td>
               </tr>
               <tr>
                <td colspan=3><a href='#' onclick=\"javascript:basketClear();\">clear</a></td><td align='right'><a href='#' onclick=\"javascript:basketCheckout();\"><b>CheckOut</b></a></td>
              </table>";
        
      } else {
        unset($_SESSION["ses_basket_items"]);
        unset($_SESSION["ses_basket_name"]);
        unset($_SESSION["ses_basket_amount"]);
        unset($_SESSION["ses_basket_price"]);
        unset($_SESSION["ses_basket_id"]);
        unset($_SESSION["ses_basket_forCorp"]);
        unset($_SESSION["ses_basket_corpOnly"]);
      }
       break;

    case 'mini':
      if ($_SESSION["ses_basket_items"]>0){
      $total = 0;
         for ($basket_counter=0;$basket_counter<$_SESSION["ses_basket_items"];$basket_counter++){
            $price = $_SESSION["ses_basket_price"][$basket_counter] * $_SESSION["ses_basket_amount"][$basket_counter];
            $total+=$price;
         }
         $total = number_format($total, 2, '.', ','). " ISK";
         echo "<a href=\"#\" onclick='javascript:basketShow();'><span class='ui-icon ui-icon-cart'></span> " . $_SESSION["ses_basket_items"] . " Item(s)</a> : <b id='miniTotal'>$total</b>";

      } else {
        unset($_SESSION["ses_basket_items"]);
        unset($_SESSION["ses_basket_name"]);
        unset($_SESSION["ses_basket_amount"]);
        unset($_SESSION["ses_basket_price"]);
        unset($_SESSION["ses_basket_id"]);
        unset($_SESSION["ses_basket_forCorp"]);
        unset($_SESSION["ses_basket_corpOnly"]);
        echo "<span class='ui-icon ui-icon-cart'></span> empty";
      }
       break;
       
    case 'clear':
        unset($_SESSION["ses_basket_items"]);
        unset($_SESSION["ses_basket_name"]);
        unset($_SESSION["ses_basket_amount"]);
        unset($_SESSION["ses_basket_price"]);
        unset($_SESSION["ses_basket_id"]);
        unset($_SESSION["ses_basket_forCorp"]);
        unset($_SESSION["ses_basket_corpOnly"]);
       break;
       
    case 'checkout':
        if ($_SESSION["logged_in"] && $_SESSION["ses_basket_items"]>0){

          $order = new order();
          $args = array(
              'userID' => $_SESSION["userID"],
              'forCorp' => $_SESSION["ses_basket_forCorp"],
              'corpOnly' => $_SESSION["ses_basket_corpOnly"],
              'items' => $_SESSION["ses_basket_id"],
              'quantity' => $_SESSION["ses_basket_amount"],
              'price' => $_SESSION["ses_basket_price"],
              'nbitem' => $_SESSION["ses_basket_items"],
            );

          if($order->create($args)){
            echo "Order successfull";
            unset($_SESSION["ses_basket_items"]);
            unset($_SESSION["ses_basket_name"]);
            unset($_SESSION["ses_basket_amount"]);
            unset($_SESSION["ses_basket_price"]);
            unset($_SESSION["ses_basket_id"]);
            unset($_SESSION["ses_basket_forCorp"]);
            unset($_SESSION["ses_basket_corpOnly"]);
          }
          else echo 'Order Failed !!';

        }
       break;
       

  
  
  
  }
}

if (isset($_POST['trade'])) {
  mysql_select_db(DB_NAME,$db)  or die('Erreur de selection '.mysql_error());
  switch ($_POST['trade']){
    case 'list_minerals':
      $sql = "SELECT typeID, eveIcons.iconFile , invTypes.typeName,quantity, supplyType, lowLimit,
      (((lowLimit)*3)/2)-quantity as toOrder, min(items_selling.price) as price
      FROM `FedMart_matosAM`
      INNER JOIN invTypes USING (typeID)
      INNER JOIN eveIcons USING (iconID)
      left join items_selling on (invTypes.typeID=items_selling.type_id)
      WHERE quantity IS NOT NULL
      AND lowLimit > 0
      AND items_selling.region_id='10000002'
      AND supplyType = 1
      GROUP BY typeID
      HAVING quantity <= (((lowLimit)*3)/2)
      ";
      $req = mysql_query($sql) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());
      $data = "";
      $mail = "<br />Minerals (Minage/Trade) :<br />\r\n";
      $total=0;
      while ($item = mysql_fetch_object($req)){
        $icon = "<img src=\"".utils::get_icon($item->iconFile,64)."\" title=\"$item->typeName\" class='ricon'>";
        $qty = number_format(speround((int)$item->toOrder), 0, '.', ' ');
        $data .= "<li>$icon $qty x ".$item->typeName."</li>";
        $mail .= "$qty x <a href='showinfo:$item->typeID'>$item->typeName</a><br />\r\n";
        $price = speround((int)$item->toOrder) * $item->price;
        $total += $price;

      }
      
      Header("content-type: application/xml");
      echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
      echo "<compo><data><![CDATA[";
      echo "<ul>$data</ul>";
      echo "]]></data>";
      echo "<price>$total</price>
      <mail><![CDATA[$mail]]></mail>
      </compo>";
    break;

    case 'list_materials':
      $sql = "SELECT typeID, eveIcons.iconFile , invTypes.typeName,quantity, supplyType, lowLimit,
      (((lowLimit)*3)/2)-quantity as toOrder, quantity/lowLimit as priority, min(items_selling.price) as price
      FROM `FedMart_matosAM`
      INNER JOIN invTypes USING (typeID)
      INNER JOIN eveIcons USING (iconID)
      left join items_selling on (invTypes.typeID=items_selling.type_id)
      WHERE quantity IS NOT NULL
      AND lowLimit > 0
      AND items_selling.region_id='10000002'
      AND supplyType = 2
      GROUP BY typeID
      HAVING quantity <= (((lowLimit)*3)/2)
      ORDER BY priority";
      $req = mysql_query($sql) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());
      $data = "";

      $sqlcompo = "SELECT typeID, eveIcons.iconFile , invTypes.typeName,quantity, supplyType, lowLimit,
      (((lowLimit)*3)/2)-quantity as toOrder, quantity/lowLimit as priority, invBlueprintTypes.blueprintTypeID
      FROM `FedMart_matosAM`
      INNER JOIN invTypes USING (typeID)
      INNER JOIN eveIcons USING (iconID)
      INNER JOIN invBlueprintTypes ON productTypeID=typeID
      WHERE quantity IS NOT NULL
      AND lowLimit > 0
      AND supplyType = 5
      HAVING quantity <= lowLimit
      ORDER BY priority";
      $reqcompo = mysql_query($sqlcompo) or die('Erreur SQL !<br>'.$sqlcompo.'<br>'.mysql_error());




      while ($item = mysql_fetch_object($req)){

        if(isset($matos["$item->typeID"]))
          $matos["$item->typeID"]["quantity"] += $item->toOrder;
        else
          $matos["$item->typeID"] = array (
            "id" => "$item->typeID",
            "name" => "$item->typeName",
            "quantity" => $item->toOrder,
            "icon" => "$item->iconFile",
            "price" => $item->price

          );

      }

      while ($compo = mysql_fetch_object($reqcompo)){
        $sqlmatos = "SELECT invTypes.typeID, invTypes.typeName, invTypeMaterials.quantity, eveIcons.iconFile, min(items_selling.price) as price
        FROM `invTypeMaterials` 
        LEFT JOIN invTypes ON invTypes.typeID=`invTypeMaterials`.materialTypeID 
        LEFT JOIN eveIcons ON invTypes.iconID=eveIcons.iconID
        LEFT JOIN invGroups ON invTypes.groupID=invGroups.groupID
        left join items_selling on (invTypes.typeID=items_selling.type_id)
        WHERE `invTypeMaterials`.typeID = '$compo->typeID'
        AND items_selling.region_id='10000002'
        GROUP BY typeID";
        $reqmatos = mysql_query($sqlmatos) or die('Erreur SQL !<br>'.$sqlmatos.'<br>'.mysql_error());

        while ($item = mysql_fetch_object($reqmatos)){
          if(isset($matos["$item->typeID"]))
            $matos["$item->typeID"]["quantity"] += ($item->quantity * $compo->toOrder);
          else
            $matos["$item->typeID"] = array (
              "id" => "$item->typeID",
              "name" => "$item->typeName",
              "quantity" => ($item->quantity * $compo->toOrder),
              "icon" => "$item->iconFile",
              "price" => $item->price

            );

        }


      }

      $total = 0;
      $mail = "<br />Materials (trade) :<br />\r\n";
      foreach ($matos as $itemID => $Uitem){
        $icon = "<img src=\"".utils::get_icon($Uitem['icon'],64)."\" title=\"".$Uitem['name']."\" class='ricon'>";
        $qty = number_format(speround((int)$Uitem['quantity']), 0, '.', ' ');
        $price = speround((int)$Uitem['quantity']) * $Uitem['price'];
        $total += $price;
        if($inEVE){
          $evelink = "<a href=\"#\" onclick=\"CCPEVE.showMarketDetails($itemID)\">".$Uitem['name']."</a>";
        }
        else $evelink = $Uitem['name'];

        $data .= "<li>$icon $qty x $evelink</li><!--$price-->";
        $mail .= "$qty x <a href='showinfo:$itemID' >".$Uitem['name']."</a><br />\r\n";
       }

      /*foreach blabla
        $icon = "<img src=\"".ICON_32."/icon".$item->iconFile.".png\" title=\"$item->typeName\" class='ricon'>";
        $qty = number_format(speround((int)$item->toOrder), 0, '.', ' ');
        if($inEVE){
          $evelink = "<a href=\"#\" onclick=\"CCPEVE.showMarketDetails($item->typeID)\">$item->typeName</a>";
        }
        else $evelink = "$item->typeName";

        $data .= "<li>$icon $qty x $evelink</li>";
      */
      //$total = number_format(speround($total), 0, '.', ' ');
      //echo "<ul>$data</ul> <!--$total-->";
      Header("content-type: application/xml");
      echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
      echo "<compo><data><![CDATA[";
      echo "<ul>$data</ul>";
      echo "]]></data>";
      echo "<price>$total</price>
      <mail><![CDATA[$mail]]></mail>
      </compo>";
    break;

    case 'list_goods':
      $sql = "SELECT typeID, eveIcons.iconFile , invTypes.typeName,quantity, supplyType, lowLimit,
      (((lowLimit)*3)/2)-quantity as toOrder, quantity/lowLimit as priority, min(items_selling.price) as price
      FROM `FedMart_matosAM`
      INNER JOIN invTypes USING (typeID)
      INNER JOIN eveIcons USING (iconID)
      left join items_selling on (invTypes.typeID=items_selling.type_id)
      WHERE quantity IS NOT NULL
      AND lowLimit > 0
      AND items_selling.region_id='10000002'
      AND supplyType = 3
      GROUP BY typeID
      HAVING quantity < lowLimit
      ORDER BY priority";
      $req = mysql_query($sql) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());
      $data = "";
      $total = 0;
      $mail = "<br />Goods (trade):<br />\r\n";
      while ($item = mysql_fetch_object($req)){
        $icon = "<img src=\"".utils::get_icon($item->iconFile,64)."\" title=\"$item->typeName\" class='ricon'>";
        $qty = number_format(speround((int)$item->toOrder), 0, '.', ' ');
        if($inEVE){
          $evelink = "<a href=\"#\" onclick=\"CCPEVE.showMarketDetails($item->typeID)\">$item->typeName</a>";
        }
        else $evelink = "$item->typeName";
        $data .= "<li>$icon $qty x $evelink</li>";
        $mail .= "$qty x <a href='showinfo:$item->typeID'>$item->typeName</a><br />\r\n";
        $price = speround((int)$item->toOrder) * $item->price;
        $total += $price;
      }
      Header("content-type: application/xml");
      echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
      echo "<compo><data><![CDATA[";
      echo "<ul>$data</ul>";
      echo "]]></data>";
      echo "<price>$total</price>
      <mail><![CDATA[$mail]]></mail>
      </compo>";
      
    break;

    case 'list_datacore':
      $sql = "SELECT typeID, eveIcons.iconFile , invTypes.typeName,quantity, supplyType, lowLimit,
      lowLimit-quantity as toOrder, quantity/lowLimit as priority, min(items_selling.price) as price
      FROM `FedMart_matosAM`
      INNER JOIN invTypes USING (typeID)
      INNER JOIN eveIcons USING (iconID)
      left join items_selling on (invTypes.typeID=items_selling.type_id)
      WHERE quantity IS NOT NULL
      AND lowLimit > 0
      AND items_selling.region_id='10000002'
      AND supplyType = 4
      GROUP BY typeID
      HAVING quantity < lowLimit
      ORDER BY priority";
      $req = mysql_query($sql) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());
      $data = "";
      $total = 0;
      $mail = "<br />Datacores (trade) :<br />\r\n";
      while ($item = mysql_fetch_object($req)){
        $icon = "<img src=\"".utils::get_icon($item->iconFile,64)."\" title=\"$item->typeName\" class='ricon'>";
        $qty = number_format(speround((int)$item->toOrder), 0, '.', ' ');
        if($inEVE){
          $evelink = "<a href=\"#\" onclick=\"CCPEVE.showMarketDetails($item->typeID)\">$item->typeName</a>";
        }
        else $evelink = "$item->typeName";
        $data .= "<li>$icon $qty x $evelink</li>";
        $mail .= "$qty x <a href='showinfo:$item->typeID'>$item->typeName</a><br />\r\n";
        $price = speround((int)$item->toOrder) * $item->price;
        $total += $price;

      }
      Header("content-type: application/xml");
      echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
      echo "<compo><data><![CDATA[";
      echo "<ul>$data</ul>";
      echo "]]></data>";
      echo "<price>$total</price>
      <mail><![CDATA[$mail]]></mail>
      </compo>";

    break;

    case 'list_components':
      $sql = "SELECT typeID, eveIcons.iconFile , invTypes.typeName,quantity, supplyType, lowLimit,
      (((lowLimit)*3)/2)-quantity as toOrder, quantity/lowLimit as priority, invBlueprintTypes.blueprintTypeID
      FROM `FedMart_matosAM`
      INNER JOIN invTypes USING (typeID)
      INNER JOIN eveIcons USING (iconID)
      INNER JOIN invBlueprintTypes ON productTypeID=typeID
      WHERE quantity IS NOT NULL
      AND lowLimit > 0
      HAVING quantity <= lowLimit
      AND supplyType = 5
      ORDER BY priority";
      $req = mysql_query($sql) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());
      $data = "";
      $mail = "<br />Compo (prod) :<br />\r\n";
      while ($item = mysql_fetch_object($req)){
        $icon = "<img src=\"".utils::get_icon($item->iconFile,64)."\" title=\"$item->typeName\" class='ricon'>";
        $qty = number_format(speround((int)$item->toOrder), 0, '.', ' ');
        if($inEVE){
          $evelink = "<a href=\"#\" onclick=\"CCPEVE.showInfo($item->blueprintTypeID)\">$item->typeName</a>";
        }
        else $evelink = "$item->typeName";
        $data .= "<li>$icon $qty x $evelink</li>";
        $mail .= "$qty x <a href='showinfo:$item->blueprintTypeID'>$item->typeName</a><br />\r\n";

      }
      Header("content-type: application/xml");
      echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
      echo "<compo><data><![CDATA[";
      echo "<ul>$data</ul>";
      echo "]]></data>";
      echo "<price>0</price>
      <mail><![CDATA[$mail]]></mail>
      </compo>";
      
    break;

    case 'list_RAM':
      $sql = "SELECT typeID, eveIcons.iconFile , invTypes.typeName,quantity, supplyType, lowLimit,
      lowLimit-quantity as toOrder, quantity/lowLimit as priority, invBlueprintTypes.blueprintTypeID
      FROM `FedMart_matosAM`
      INNER JOIN invTypes USING (typeID)
      INNER JOIN eveIcons USING (iconID)
      INNER JOIN invBlueprintTypes ON productTypeID=typeID
      WHERE quantity IS NOT NULL
      AND lowLimit > 0
      HAVING quantity < lowLimit
      AND supplyType = 6
      ORDER BY priority";
      $req = mysql_query($sql) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());
      $data = "";
      $mail = "<br />R.A.M. (prod) :<br />\r\n";
      while ($item = mysql_fetch_object($req)){
        $icon = "<img src=\"".utils::get_icon($item->iconFile,64)."\" title=\"$item->typeName\" class='ricon'>";
        $qty = number_format(speround((int)$item->toOrder), 0, '.', ' ');
        $data .= "<li>$icon $qty x ".$item->typeName."</li>";
        $mail .= "$qty x <a href='showinfo:$item->blueprintTypeID'>$item->typeName</a><br />\r\n";

      }
      Header("content-type: application/xml");
      echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
      echo "<compo><data><![CDATA[";
      echo "<ul>$data</ul>";
      echo "]]></data>";
      echo "<price>0</price>
      <mail><![CDATA[$mail]]></mail>
      </compo>";
    break;


  }
}

if (isset($_POST['delOrder'])) {
  if ($_SESSION["logged_in"]){
  $userID = $_SESSION["userID"];
  $orderID = $_POST['delOrder'];
  
  //$delOrderSQL = "DELETE FROM FedMart_orders WHERE orderID = $orderID LIMIT 1";
  $delOrderSQL = "UPDATE FedMart_orders SET `currentStep` = '99' WHERE `orderID` = '$orderID' LIMIT 1";
  mysql_select_db(DB_NAME,$db)  or die('Erreur de selection '.mysql_error());
  $req = mysql_query($delOrderSQL) or die('FAILED');
  
  echo "OK";
  }
}

if (isset($_POST['delMOrder'])) {
  if ($_SESSION["logged_in"]){
  $userID = $_SESSION["userID"];
  $MorderID = $_POST['delMOrder'];
  
  $delMOrderSQL = "DELETE FedMart_orders, FedMart_Morders FROM FedMart_Morders LEFT JOIN FedMart_orders ON FedMart_Morders.MorderID=FedMart_orders.MorderID WHERE FedMart_Morders.MorderID = $MorderID";
  mysql_select_db(DB_NAME,$db)  or die('Erreur de selection '.mysql_error());
  $req = mysql_query($delMOrderSQL) or die('FAILED'.mysql_error());
  
  echo "OK";
  }
}

if (isset($_POST['assignOrder'])) {
  if ($_SESSION["logged_in"]){
  $userID = $_SESSION["userID"];
  $orderID = $_POST['assignOrder'];
  
  mysql_select_db(DB_NAME,$db)  or die('Erreur de selection '.mysql_error());
  $checkOrderSQL = "select FedMart_orders.orderID, FedMart_orders.currentStep from FedMart_orders WHERE FedMart_orders.orderID = '$orderID' LIMIT 1";
  $req = mysql_query($checkOrderSQL) or die('Erreur SQL !<br>'.$checkOrderSQL.'<br>'.mysql_error());
  $checkOrder = mysql_fetch_object($req);
  
  $initStep = $checkOrder->currentStep;
  if($initStep == 0) {
  
  $bpSQL = "
            select FedMart_orders.orderID, FedMart_orders.itemID, FedMart_bpAM.typeID, invBlueprintTypes.techLevel, invBlueprintTypes.wasteFactor 
            from FedMart_orders, FedMart_bpAM, invBlueprintTypes
            where FedMart_bpAM.typeID = invBlueprintTypes.blueprintTypeID
            and invBlueprintTypes.productTypeID = FedMart_orders.itemID and FedMart_orders.orderID = '$orderID' LIMIT 1";
            
  
  $req = mysql_query($bpSQL) or die('Erreur SQL !<br>'.$bpSQL.'<br>'.mysql_error());
  $bp = mysql_fetch_object($req);
  if(is_object($bp)){
    if($bp->techLevel>1)$initStep = 2;
    else {
        $bpMatosSQL = "
        SELECT invTypes.typeID, invTypes.typeName, ramTypeRequirements.quantity as qNeeded, FedMart_matosAM.quantity as qInStock, FedMart_matosAM.cost, eveIcons.iconFile  
        FROM `ramTypeRequirements` 
        LEFT JOIN invTypes ON invTypes.typeID=`ramTypeRequirements`.requiredTypeID
        LEFT JOIN FedMart_matosAM ON `ramTypeRequirements`.requiredTypeID=FedMart_matosAM.typeID
        LEFT JOIN eveIcons ON invTypes.iconID=eveIcons.iconID
        WHERE `ramTypeRequirements`.typeID = '".$bp->typeID."' AND `ramTypeRequirements`.activityID=1 AND `ramTypeRequirements`.requiredTypeID < 41
        ";
        
        $itMatosSQL = "
        SELECT invTypes.typeID, invTypes.typeName, invTypeMaterials.quantity as qNeeded, FedMart_matosAM.quantity as qInStock, FedMart_matosAM.cost, eveIcons.iconFile  
        FROM `invTypeMaterials` 
        LEFT JOIN invTypes ON invTypes.typeID=`invTypeMaterials`.materialTypeID
        LEFT JOIN FedMart_matosAM ON `invTypeMaterials`.materialTypeID=FedMart_matosAM.typeID
        LEFT JOIN eveIcons ON invTypes.iconID=eveIcons.iconID
        WHERE `invTypeMaterials`.typeID = '".$bp->itemID."' AND `invTypeMaterials`.materialTypeID < 41
        ";
        
        $reqbp = mysql_query($bpMatosSQL) or die('Erreur SQL !<br>'.$bpMatosSQL.'<br>'.mysql_error());
        $reqit = mysql_query($itMatosSQL) or die('Erreur SQL !<br>'.$itMatosSQL.'<br>'.mysql_error());
        
        $cost = 0;
        while ($line = mysql_fetch_array($reqit)){
            $curr_rsrc = round((int)$line['qNeeded'] * ((int)$bp->wasteFactor/100+1));
            if($line['qInStock']<=$curr_rsrc)$rsrc .= "<img src=\"".ICON_16."/icon".$line['iconFile'].".png\">".$line['typeName']." needed<br />";
            //echo "$curr_rsrc - ";
        }
        while ($line = mysql_fetch_array($reqbp)){
            $curr_rsrc = round((int)$line['qNeeded']);
            if($line['qInStock']<=$curr_rsrc)$rsrc .= "<img src=\"".ICON_16."/icon".$line['iconFile'].".png\">".$line['typeName']." needed<br />";
            //echo "$curr_rsrc - ";
        }
        if(isset($rsrc))$initStep = 5;
        else $initStep = 6;

    }
    
  }
  else $initStep = 1;
  
  }
  
  $assignSQL = "UPDATE FedMart_orders SET `assignedTo`='$userID', `currentStep` = '$initStep' WHERE `orderID` = '$orderID' LIMIT 1";

  //echo $assignSQL;
  mysql_select_db(DB_NAME,$db)  or die('Erreur de selection '.mysql_error());
  $req = mysql_query($assignSQL) or die('FAILED');
  
  echo "OK";
  }
}

if (isset($_POST['unassignOrder'])) {
  if ($_SESSION["logged_in"]){
  $userID = $_SESSION["userID"];
  $orderID = $_POST['unassignOrder'];
  
  $assignSQL = "UPDATE FedMart_orders SET `assignedTo`= NULL WHERE `orderID` = '$orderID' LIMIT 1";

  //echo $assignSQL;
  mysql_select_db(DB_NAME,$db)  or die('Erreur de selection '.mysql_error());
  $req = mysql_query($assignSQL) or die('FAILED');
  
  echo "OK";
  }
}

if (isset($_POST['changeStep'])) {
  if ($_SESSION["logged_in"]){
  $userID = $_SESSION["userID"];
  $orderID = $_POST['changeStep'];
  $step = $_POST['step'];
  
  if($step==10)$endTime = ", `endTime`= NOW() ";
  
  $nextStepSQL = "UPDATE FedMart_orders SET `currentStep`='$step' $endTime WHERE `orderID` = '$orderID' LIMIT 1";

  //echo $assignSQL;
  mysql_select_db(DB_NAME,$db)  or die('Erreur de selection '.mysql_error());
  $req = mysql_query($nextStepSQL) or die('FAILED');
  
  echo "OK";
  }
}

if (isset($_POST['editPrice'])) {
  if ($_SESSION["logged_in"]){
  $orderID = $_POST['editPrice'];
  $newprice = $_POST['price'];
  
  $sqlSel = "SELECT * FROM FedMart_orders WHERE `orderID` = '$orderID' LIMIT 1";
  mysql_select_db(DB_NAME,$db)  or die('Erreur de selection '.mysql_error());
  $req = mysql_query($sqlSel) or die('FAILED Sel');
  $order = mysql_fetch_object($req);
  
  $newprice = (float)$newprice /  $order->quantity;
  //echo "$newprice";
  $sql = "UPDATE FedMart_orders SET `cost`='$newprice' WHERE `orderID` = '$orderID' LIMIT 1";
  $req = mysql_query($sql) or die('FAILED Upd');
  
  echo "OK";
  }
}

if (isset($_POST['plSelector'])) {
  if ($_SESSION["logged_in"]){
    $prodRight =  $_SESSION["prodRight"];
    $plSelector = "<div class='pl_sel_icon' id='ALL' title='All item'  style='background-image: url(\"".ICON."items/57_64_10.png\");'><a href='#!/PL'><div class='selector'></div></a></div>";
    $plSelector .= ($prodRight&1) ? "<div class='pl_sel_icon' id='TI_mod' title='Modules TI' style='background-image: url(\"".ICON."items/12_64_3.png\");'><a href='#!/PL/TI_mod'><div class='selector'></div></a></div>" : "" ;
    $plSelector .= (($prodRight>>1)&1) ? "<div class='pl_sel_icon' id='TII_mod' title='Modules TII' style='background-image: url(\"".ICON."items/12_64_3.png\");'><div class='t2' style='background-image: url(\"".ICON."items/38_16_141.png\");'><a href='#!/PL/TII_mod'><div class='selector'></div></a></div></div>" : "" ;
    $plSelector .= ($prodRight>>2&1) ? "<div class='pl_sel_icon' id='TI_rig' title='Rigs TI' style='background-image: url(\"".ICON."items/68_64_4.png\");'><a href='#!/PL/TI_rig'><div class='selector'></div></a></div>" : "" ;
    $plSelector .= ($prodRight>>3&1) ? "<div class='pl_sel_icon' id='TII_rig' title='Rigs TII' style='background-image: url(\"".ICON."items/68_64_4.png\");'><div class='t2' style='background-image: url(\"".ICON."items/38_16_141.png\");'><a href='#!/PL/TII_rig'><div class='selector'></div></a></div></div>" : "" ;
    $plSelector .= ($prodRight>>4&1) ? "<div class='pl_sel_icon' id='TI_ship' title='Ships TI' style='background-image: url(\"".ICON."items/9_64_5.png\");'><a href='#!/PL/TI_ship'><div class='selector'></div></a></div>" : "" ;
    $plSelector .= ($prodRight>>5&1) ? "<div class='pl_sel_icon' id='TII_ship' title='Ships TII' style='background-image: url(\"".ICON."items/9_64_5.png\");'><div class='t2' style='background-image: url(\"".ICON."items/38_16_141.png\");'><a href='#!/PL/TII_ship'><div class='selector'></div></a></div></div>" : "" ;
    $plSelector .= ($prodRight>>6&1) ? "<div class='pl_sel_icon' id='TIII_ship' title='Ships TIII' style='background-image: url(\"".ICON."items/9_64_5.png\");'><div class='t2' style='background-image: url(\"".ICON."items/38_16_142.png\");'><a href='#!/PL/TIII_ship'><div class='selector'></div></a></div></div>" : "" ;
//    $plSelector .= ($prodRight->TI_cap) ? "<div class='pl_sel_icon' id='TI_cap' style='background-image: url(\"".IMG_DIR."/icons/64_64/icon56_03.png\");'><a href='#!/PL/TI_chips'><div class='selector'></div></a></div>" : "" ;
 //   $plSelector .= ($prodRight->TII_cap) ? "<div class='pl_sel_icon' id='TII_cap' style='background-image: url(\"".IMG_DIR."/icons/64_64/icon56_03.png\");'><div class='t2' style='background-image: url(\"".IMG_DIR."/icons/16_16/icon38_141.png\");'><a href='#!/PL/TII_chip'><div class='selector'></div></a></div></div>" : "" ;





    echo $plSelector;

  }
}

if (isset($_POST['listorder'])) {
  if ($_SESSION["logged_in"]){
    $userID = $_SESSION["userID"];
    $logged_in = $_SESSION["logged_in"];
    $whichOrder = $_POST['listorder'];
    $userRole = $_SESSION["role"]; 
    $userCorp = $_SESSION["corp"];
    $filter = "1";
    $prodRight =  $_SESSION["prodRight"];
    $limit = "(";

    $limit .= ($prodRight&1) ? "(techLevel = 1 AND categoryID IN (7,8,18,22) AND groupName NOT LIKE 'Rig%') OR " : "" ;
    $limit .= ($prodRight>>1&1) ? "(techLevel = 2 AND categoryID IN (7,8,18,22) AND groupName NOT LIKE 'Rig%') OR " : "" ;
    $limit .= ($prodRight>>2&1) ? "(techLevel = 1 AND categoryID IN (7,8,18,22) AND groupName LIKE 'Rig%') OR " : "" ;
    $limit .= ($prodRight>>3&1) ? "(techLevel = 2 AND categoryID IN (7,8,18,22) AND groupName LIKE 'Rig%') OR " : "" ;
    $limit .= ($prodRight>>4&1) ? "(techLevel = 1 AND categoryID = 6) OR " : "" ;
    $limit .= ($prodRight>>5&1) ? "(techLevel = 2 AND categoryID = 6) OR " : "" ;
    $limit .= ($prodRight>>6&1) ? "(techLevel = 3 AND categoryID IN (6,32)) OR " : "" ;
    $limit .= ($prodRight>>9&1) ? "(categoryID = 17) OR " : "" ;

    if(strlen($limit)>3) $limit = substr($limit,0,-4);
    else $limit .= "0";
    $limit .= ")";
    
    if(isset($_POST['filter'])){
       switch($_POST{'filter'}){
         case 'TI_mod':
   $filter = "(techLevel = 1 AND categoryID IN (7,8,18) AND groupName NOT LIKE 'Rig%')";

   break;

   case 'TII_mod':
   $filter = "(techLevel = 2 AND categoryID IN (7,8,18) AND groupName NOT LIKE 'Rig%')";

   break;

   case 'TI_rig':
   $filter = "(techLevel = 1 AND categoryID IN (7,8,18) AND groupName LIKE 'Rig%')";
   break;

   case 'TII_rig':
   $filter = "(techLevel = 2 AND categoryID IN (7,8,18) AND groupName LIKE 'Rig%')";
   break;

   case 'TI_ship':
   $filter = "(techLevel = 1 AND categoryID = 6)";
   break;

   case 'TII_ship':
   $filter = "(techLevel = 2 AND categoryID = 6)";
   break;

   case 'TIII_ship':
   $filter = "(techLevel = 3 AND categoryID IN (6,32))";
   break;

    case 'compo':
    $filter = "(categoryID = 17)";
    break;




       }
    }
    switch ($whichOrder){
      case 'ALLactive':
        switch ($userRole){
          case 2:
          case 3:
          case 4:
            $myOrderSQL = "SELECT orderID, MorderID, FedMart_users.character ,FedMart_users.characterID ,FedMart_users.corpID, FedMart_corp.corpName, FedMart_corp.corpTick , quantity, cost, startTime, endTime, assignedTo, currentStep,forCorp,corpOnly, invTypes.typeName, invTypes.typeID, invBlueprintTypes.techLevel,invBlueprintTypes.blueprintTypeID, invGroups.categoryID, eveIcons.iconFile
                       FROM `FedMart_orders`
                       LEFT JOIN invTypes ON itemID=invTypes.typeID
                       LEFT JOIN invBlueprintTypes ON itemID=invBlueprintTypes.productTypeID
                       LEFT JOIN FedMart_users ON owner=characterID
                       LEFT JOIN FedMart_corp ON FedMart_users.corpID=FedMart_corp.corpID
                       LEFT JOIN invGroups ON invTypes.groupID=invGroups.groupID
                       LEFT JOIN eveIcons ON invTypes.iconID=eveIcons.iconID
                       WHERE currentStep < '10' AND (assignedTo is NULL OR assignedTo = '$userID') 
                       AND NOT (FedMart_users.corpID != '$userCorp' AND corpOnly = 1)
                       AND $filter AND $limit
                       ORDER BY assignedTo DESC, FedMart_users.character ASC,  orderID ASC
                       ";
            break;
          
          case 5:
            $myOrderSQL = "SELECT orderID, MorderID, FedMart_users.character ,FedMart_users.characterID ,FedMart_users.corpID, FedMart_corp.corpName, FedMart_corp.corpTick ,quantity, cost, startTime, endTime, assignedTo, currentStep, forCorp,corpOnly, invTypes.typeName, invTypes.typeID, invBlueprintTypes.techLevel,invBlueprintTypes.blueprintTypeID, invGroups.categoryID, eveIcons.iconFile
                       FROM `FedMart_orders`
                       LEFT JOIN invTypes ON itemID=invTypes.typeID
                       LEFT JOIN invBlueprintTypes ON itemID=invBlueprintTypes.productTypeID
                       LEFT JOIN FedMart_users ON owner=characterID
                       LEFT JOIN FedMart_corp ON FedMart_users.corpID=FedMart_corp.corpID
           LEFT JOIN invGroups ON invTypes.groupID=invGroups.groupID
                       LEFT JOIN eveIcons ON invTypes.iconID=eveIcons.iconID
                       WHERE currentStep < '10' 
           AND $filter AND $limit
                       ORDER BY assignedTo DESC, FedMart_users.character ASC,  orderID ASC
                       ";
                      
            break;
        }
                       
                       
        $assignSQL = "SELECT FedMart_orders.orderID, FedMart_users.character, FedMart_users.corpID
                      FROM FedMart_orders, FedMart_users
                      WHERE FedMart_users.characterID=FedMart_orders.assignedTo";
        
        mysql_select_db(DB_NAME,$db)  or die('Erreur de selection '.mysql_error());
        $req = mysql_query($myOrderSQL) or die('Erreur SQL !<br>'.$myOrderSQL.'<br>'.mysql_error());
        $req1 = mysql_query($assignSQL) or die('Erreur SQL !<br>'.$assignSQL.'<br>'.mysql_error());

        while ($assigned = mysql_fetch_object($req1)){
          $Uassign[$assigned->orderID] = array( 
      'name' => $assigned->character,
      'corpid' => $assigned->corpID);

        }
        echo "<ul>";
        while ($order = mysql_fetch_object($req)){
//          echo "$order->character : $order->quantity x $order->typeName Status : $order->currentStep <br />";
          switch($order->currentStep) {
            case 0:
              $status = "<img src=\"img/level0.gif\"><i> waiting for processing ...</i>";
              $Astep = "step <a href=\"#\" onclick='javascript:OrderChangeStep($order->orderID, 1);'>>></a>";
              break;
            
            case 1:
              $status = "<img src=\"img/level1_act.gif\"><i> looking for BP ...</i>";
              $Astep = "<a href=\"#\" onclick='javascript:OrderChangeStep($order->orderID, 0);'><<</a> step <a href=\"#\" onclick='javascript:OrderChangeStep($order->orderID, 2);'>>></a>";
              break;
        
            case 2:
              $status = "<img src=\"img/level1.gif\"><i> waiting for research line</i>";
              $Astep = "<a href=\"#\" onclick='javascript:OrderChangeStep($order->orderID, 1);'><<</a> step <a href=\"#\" onclick='javascript:OrderChangeStep($order->orderID, 3);'>>></a>";
              break;
        
            case 3:
              $status = "<img src=\"img/level2_act.gif\"><i> research in progress ...</i>";
              $Astep = "<a href=\"#\" onclick='javascript:OrderChangeStep($order->orderID, 2);'><<</a> step <a href=\"#\" onclick='javascript:OrderChangeStep($order->orderID, 4);'>>></a>";
              break;
              
            case 4:
              $status = "<img src=\"img/level2.gif\"><i> research complete</i>";
              $Astep = "<a href=\"#\" onclick='javascript:OrderChangeStep($order->orderID, 3);'><<</a> step <a href=\"#\" onclick='javascript:OrderChangeStep($order->orderID, 5);'>>></a>";
              break;
              
            case 5:
              $status = "<img src=\"img/level3_act.gif\"><i> looking for materials ...</i>";
              $Astep = "<a href=\"#\" onclick='javascript:OrderChangeStep($order->orderID, 4);'><<</a> step <a href=\"#\" onclick='javascript:OrderChangeStep($order->orderID, 6);'>>></a>";
              break;
              
            case 6:
              $status = "<img src=\"img/level3.gif\"><i> waiting for production line ...</i>";
              $Astep = "<a href=\"#\" onclick='javascript:OrderChangeStep($order->orderID, 5);'><<</a> step <a href=\"#\" onclick='javascript:OrderChangeStep($order->orderID, 7);'>>></a>";
              break;
        
            case 7:
              $status = "<img src=\"img/level4_act.gif\"><i> production in progress ...</i>";
              $Astep = "<a href=\"#\" onclick='javascript:OrderChangeStep($order->orderID, 6);'><<</a> step <a href=\"#\" onclick='javascript:OrderChangeStep($order->orderID, 8);'>>></a>";
              break;
        
            case 8:
              $status = "<img src=\"img/level4.gif\"><i> production complete</i>";
              $Astep = "<a href=\"#\" onclick='javascript:OrderChangeStep($order->orderID, 7);'><<</a> step <a href=\"#\" onclick='javascript:OrderChangeStep($order->orderID, 9);'>>></a>";
              break;
              
            case 9:
              $status = "<img src=\"img/level5_act.gif\"><i> delivering ...</i>";
              $Astep = "<a href=\"#\" onclick='javascript:OrderChangeStep($order->orderID, 8);'><<</a> step <a href=\"#\" onclick='javascript:OrderChangeStep($order->orderID, 10);'>>></a>";
              break;
              
            case 10:
              $status = "<img src=\"img/level5.gif\"><i> completed</i>";
              break;

            case 99:
              $status = "<img src=\"img/level0.gif\"><i> canceled</i>";
              break;
              
          default:
              $status = "<img src=\"img/level0.gif\"><i> status unknown ?!</i>";
          }
          /*
          switch($order->techLevel) {
            case 2:
              $iconTech = "<div style=\"width: 16px; height: 16px; background-image: url('".ICON_16."/icon38_141.png');\"></div>";
              break;

            case 3:
              $iconTech = "<div style=\"width: 16px; height: 16px; background-image: url('".ICON_16."/icon38_142.png');\"></div>";
              break;
                          
            default:
              $iconTech = "";
          }*/
/*
          switch($order->categoryID) {
                case 6:
      $ITicon = "<div title=\"$order->typeName\" class='rportrait' style='background-image: url(\"".IMG_DIR."/types/shiptypes_png/64_64/".$order->typeID.".png\");'>$iconTech</div>";
                        //$ITicon = "<img src=\"".IMG_DIR."/types/shiptypes_png/64_64/$order->typeID.png\" class='ricon'>";
                        break;

                case 9:
      $ITicon = "<div title=\"$order->typeName\" class='rportrait' style='background-image: url(\"".IMG_DIR."/blueprints/".$order->typeID.".png\");'>$iconTech</div>";
                        //$ITicon = "<img src=\"".IMG_DIR."/blueprints/$order->typeID.png\" class='ricon'>";
                        break;

                case 18:
      $ITicon = "<div title=\"$order->typeName\" class='rportrait' style='background-image: url(\"".IMG_DIR."/types/dronetypes_png/64_64/".$order->typeID.".png\");'>$iconTech</div>";
                        //$ITicon = "<img src=\"".IMG_DIR."/types/dronetypes_png/64_64/$order->typeID.png\" class='ricon'>";
                        break;

                case 23:
      $ITicon = "<div title=\"$order->typeName\" class='rportrait' style='background-image: url(\"".IMG_DIR."/types/structuretypes_png/64_64/".$order->typeID.".png\");'>$iconTech</div>";
                        //$ITicon = "<img src=\"".IMG_DIR."/types/structuretypes_png/64_64/$order->typeID.png\" class='ricon'>";
                        break;

                case 22:
      $ITicon = "<div title=\"$order->typeName\" class='rportrait' style='background-image: url(\"".IMG_DIR."/types/deployabletypes_png/64_64/".$order->typeID.".png\");'>$iconTech</div>";
                        //$ITicon = "<img src=\"".IMG_DIR."/types/deployabletypes_png/64_64/$order->typeID.png\" class='ricon'>";
                        break;

                case 40:
      $ITicon = "<div title=\"$order->typeName\" class='rportrait' style='background-image: url(\"".IMG_DIR."/types/sovereigntystructurestypes_png/64_64/".$order->typeID.".png\");'>$iconTech</div>";
                        //$ITicon = "<img src=\"".IMG_DIR."/types/sovereigntystructurestypes_png/64_64/$order->typeID.png\" class='ricon'>";
                        break;

                case 41:
      $ITicon = "<div title=\"$order->typeName\" class='rportrait' style='background-image: url(\"".IMG_DIR."/types/planetaryinteractiontypes_png/64_64/".$order->typeID.".png\");'>$iconTech</div>";
                        //$ITicon = "<img src=\"".IMG_DIR."/types/planetaryinteractiontypes_png/64_64/$order->typeID.png\" class='ricon'>";
                        break;

                default:
                        if($order->iconFile)$ITicon = "<div title=\"$order->typeName\" class='rportrait' style='background-image: url(\"".IMG_DIR."/icons/64_64/icon".$order->iconFile.".png\");'>$iconTech</div>";
//$ITicon = "<img src=\"".IMG_DIR."/icons/64_64/icon".$order->iconFile.".png\" class='ricon'>";
      else $ITicon = "<div title=\"$order->typeName\" class='rportrait' style='background-image: url(\"".IMG_DIR."/icons/64_64/icon07_15.png\");'>$iconTech</div>";
                        //else $ITicon = "<img src=\"".IMG_DIR."/icons/64_64/icon07_15.png\" class='ricon'>";
          }*/
          $ITicon = "<div title=\"$order->typeName\" class='rportrait' style='background-image: url(\"".IMG_DIR."/Types/".$order->typeID."_64.png\");'></div>";

          
          
          $icon = "<div title=\"$order->character\" class='rportrait' style='background-image: url(\"".get_portrait($order->characterID,64)."\");'></div>";
          if($order->forCorp)$icon = "<div style='background-image: url(\"".get_logo($order->corpID,64)."\");' title=\"$order->character for $order->corpName\" class='rportrait'></div>";
          
          $cost = $order->quantity * $order->cost;
    $reduc = 0;
          if(!$order->assignedTo){
            $assignTo = "Not assigned yet";
            $takeAssign = "<a href=\"#\" onclick='javascript:TakeAssign($order->orderID);'>take assignment</a>";
          }
          else {
            $assignTo = "Assigned to ".$Uassign[$order->orderID]['name'];
            $takeAssign = "";
            if($order->assignedTo == $userID)
              $takeAssign = "<a href=\"#\" onclick='javascript:ReleaseAssign($order->orderID);'>release assignment</a>";
            
      if($order->corpID == $Uassign[$order->orderID]['corpid']){
    $reduc = 5/100;
    $cost = $cost - $cost*$reduc;
      } 
          }
          if($inEVE) $itemName = "<a href=\"#\" onclick=\"CCPEVE.showInfo($order->blueprintTypeID)\">$order->typeName</a>";
          else $itemName = "<a href=\"#\" onclick=\"javascript:showInfo($order->typeID);go('Market');\">$order->typeName</a>";
          
          echo "<li id='order_$order->orderID' ";if(!$order->assignedTo)echo "class='unassigned'";if($order->assignedTo != $userID)echo "class='assignOther'"; echo " >$icon $ITicon
          $order->quantity x $itemName<span class='action'>";if($order->assignedTo == $userID || $userRole == 5) echo "<a href=\"#\" onclick='javascript:cancelOrder($order->orderID);'>cancel order</a>"; echo "</span><br />
          <span id='assigned'>$assignTo<span class='action'>$takeAssign</span></span><br />
          Price : <span id=\"price\">".number_format($cost, 2, '.', ',')." ISK";if($reduc)echo " (-".($reduc*100)."%)";echo "</span><span class='action'>"; if($order->assignedTo == $userID || $userRole == 5)echo "<a href=\"#\" onclick='javascript:editOPrice($order->orderID);'>edit price</a>"; echo "</span><br />
          Status : <span id='status'>$status<span class='action'>"; if($order->assignedTo == $userID)echo $Astep; echo "</span></span><br />".
          (($order->corpOnly) ? "<span style='color:red;'>[$order->corpTick]</span> " : "[$order->corpTick] ") . $order->character;
          if($order->forCorp) echo " for $order->corpName";
          
          echo "<span class='action'>order n°$order->MorderID</span></li>";
        }
        echo "</ul>";        
        break;
        
      case 'ALLcompleted':
        $myOrderSQL = "SELECT orderID, FedMart_users.characterID ,FedMart_users.character, FedMart_corp.corpName , FedMart_corp.corpID, cost, forCorp, corpOnly, quantity, startTime, endTime, assignedTo, currentStep, invTypes.typeName
                       FROM `FedMart_orders`
                       LEFT JOIN invTypes ON itemID=invTypes.typeID
                       LEFT JOIN FedMart_users ON owner=characterID
                       LEFT JOIN FedMart_corp ON FedMart_users.corpID=FedMart_corp.corpID 
                       WHERE currentStep = '10'
                      AND NOT (FedMart_users.corpID != '$userCorp' AND corpOnly = 1)
                       ORDER BY endTime DESC
                       LIMIT 18
                       ";
        mysql_select_db(DB_NAME,$db)  or die('Erreur de selection '.mysql_error());
        $req = mysql_query($myOrderSQL) or die('Erreur SQL !<br>'.$myOrderSQL.'<br>'.mysql_error());
        
        echo "<ul>";
        while ($order = mysql_fetch_object($req)){
          //echo "$order->character : $order->quantity x $order->typeName Status : $order->currentStep <br />";
          $cost = $order->quantity * $order->cost;
          $icon = "<img src='".get_portrait($order->characterID)."' title=\"$order->character\" width='16px' class='ricon'>";
          if($order->forCorp)$icon = "<img src='".get_logo($order->corpID,16)."' title=\"$order->character for $order->corpName\" width='16px' class='ricon'>";
          $text = "$order->quantity x $order->typeName - ".number_format($cost, 2, '.', ',')." ISK - ".date("d M Y",strtotime($order->endTime));
          if(strlen($text)>62) $text = substr($text,0,59) . "...";
          echo "<li id='order_$order->orderID'>$icon $text
          </li>";
        }
        echo "</ul>";
        break;
        
      case 'MyActive':
       // $datenow = $_SERVER['REQUEST_TIME'];
        $myOrderSQL = "SELECT FedMart_Morders.MorderID, FedMart_users.character ,FedMart_orders.forCorp, FedMart_corp.corpName, FedMart_orders.quantity, FedMart_orders.cost, FedMart_orders.startTime, FedMart_orders.endTime, FedMart_orders.assignedTo, FedMart_orders.currentStep, FedMart_orders.itemID, invTypes.typeName, invGroups.categoryID, eveIcons.iconFile
                       FROM `FedMart_Morders`
                       LEFT JOIN FedMart_orders ON FedMart_Morders.MorderID=FedMart_orders.MorderID
                       LEFT JOIN invTypes ON FedMart_orders.itemID=invTypes.typeID
                       LEFT JOIN invGroups ON invTypes.groupID=invGroups.groupID
                       LEFT JOIN eveIcons ON invTypes.iconID=eveIcons.iconID 
                       LEFT JOIN FedMart_users ON FedMart_orders.owner=FedMart_users.characterID
                       LEFT JOIN FedMart_corp ON FedMart_users.corpID=FedMart_corp.corpID 
                       WHERE FedMart_Morders.owner='$userID' AND FedMart_orders.currentStep < '10'";
    
        $assignSQL = "SELECT FedMart_orders.orderID, FedMart_users.character, FedMart_users.corpID
                      FROM FedMart_orders, FedMart_users
                      WHERE FedMart_users.characterID=FedMart_orders.assignedTo";
        
        mysql_select_db(DB_NAME,$db)  or die('Erreur de selection '.mysql_error());
        $req = mysql_query($myOrderSQL) or die('Erreur SQL !<br>'.$myOrderSQL.'<br>'.mysql_error());
        $req1 = mysql_query($assignSQL) or die('Erreur SQL !<br>'.$assignSQL.'<br>'.mysql_error());
        
        while ($assigned = mysql_fetch_object($req1)){
          $Uassign[$assigned->orderID] = array(
        'name' => $assigned->character,
        'corpid' => $assigned->corpID);
        }

        echo "<ul>";
        while ($order = mysql_fetch_object($req)){
          if($Morder == $order->MorderID) {
            $nbItem++;
            $totalPrice += $order->quantity * $order->cost;
            $totalStep += $order->currentStep;
          }
          else {
            if(isset($Morder)){
              echo $toPrint;
            }
            $Morder = $order->MorderID;
            $totalPrice = $order->quantity * $order->cost;
            $nbItem = 1;
            $totalStep = $order->currentStep;
          }
          $totalStep = ceil($totalStep / $nbItem);
          switch($totalStep) {
              case 0:
                $status = "<i><a href=\"#\" onclick=\"javascript:cancelMOrder($order->MorderID);\">cancel?</a></i> <img src=\"img/level0.gif\">";
                break;
              
              case 1:
                $status = "<img src=\"img/level1_act.gif\">";
                break;
          
              case 2:
                $status = "<img src=\"img/level1.gif\">";
                break;
          
              case 3:
                $status = "<img src=\"img/level2_act.gif\">";
                break;
                
              case 4:
                $status = "<img src=\"img/level2.gif\">";
                break;
                
              case 5:
                $status = "<img src=\"img/level3_act.gif\">";
                break;
                
              case 6:
                $status = "<img src=\"img/level3.gif\">";
                break;
          
              case 7:
                $status = "<img src=\"img/level4_act.gif\">";
                break;
          
              case 8:
                $status = "<img src=\"img/level4.gif\">";
                break;
                
              case 9:
                $status = "<img src=\"img/level5_act.gif\">";
                break;
                
              case 10:
                $status = "<img src=\"img/level5.gif\">";
                break;
                
            default:
                $status = "<img src=\"img/level0.gif\">";
            }
          
          $comment = "no comment";
          $toPrint = "<li id='order_$order->MorderID' onclick=\"javascript:OrderDetails('$order->MorderID');\">
          order n°$order->MorderID <span class='action'>$nbItem item(s)</span><br />
          Total : ".number_format($totalPrice, 2, '.', ',')." ISK <span class='action'>$status</span><br />
          $comment
          </li>";
        }
        echo $toPrint;
        echo "</ul>";
        break;

      case 'Details':
        $MorderID = $_POST['MorderID'];
        $myOrderSQL = "SELECT FedMart_Morders.MorderID, FedMart_orders.orderID, FedMart_users.character ,FedMart_orders.forCorp, FedMart_corp.corpName, FedMart_orders.quantity, FedMart_orders.cost, FedMart_orders.startTime, FedMart_orders.endTime, FedMart_orders.assignedTo, FedMart_orders.currentStep, FedMart_orders.itemID, invTypes.typeName, invGroups.categoryID, eveIcons.iconFile
                       FROM `FedMart_Morders`
                       LEFT JOIN FedMart_orders ON FedMart_Morders.MorderID=FedMart_orders.MorderID
                       LEFT JOIN invTypes ON FedMart_orders.itemID=invTypes.typeID
                       LEFT JOIN invGroups ON invTypes.groupID=invGroups.groupID
                       LEFT JOIN eveIcons ON invTypes.iconID=eveIcons.iconID 
                       LEFT JOIN FedMart_users ON FedMart_orders.owner=FedMart_users.characterID
                       LEFT JOIN FedMart_corp ON FedMart_users.corpID=FedMart_corp.corpID 
                       WHERE FedMart_Morders.MorderID='$MorderID'";
    
        $assignSQL = "SELECT FedMart_orders.orderID, FedMart_users.character, FedMart_users.corpID
                      FROM FedMart_orders, FedMart_users
                      WHERE FedMart_users.characterID=FedMart_orders.assignedTo";
        
        mysql_select_db(DB_NAME,$db)  or die('Erreur de selection '.mysql_error());
        $req = mysql_query($myOrderSQL) or die('Erreur SQL !<br>'.$myOrderSQL.'<br>'.mysql_error());
        $req1 = mysql_query($assignSQL) or die('Erreur SQL !<br>'.$assignSQL.'<br>'.mysql_error());
        
        while ($assigned = mysql_fetch_object($req1)){
          $Uassign[$assigned->orderID] = array(
        'name' => $assigned->character,
        'corpid' => $assigned->corpID);
        }

        echo "<ul>";
        while ($order = mysql_fetch_object($req)){
          switch($order->categoryID) {
            case 6:
              $icon = "<img src=\"".IMG_DIR."/types/shiptypes_png/64_64/$order->itemID.png\" class='ricon'>";
              break;
            
            case 9:
              $icon = "<img src=\"".IMG_DIR."/blueprints/$order->itemID.png\" class='ricon'>";
              break;
        
            case 18:
              $icon = "<img src=\"".IMG_DIR."/types/dronetypes_png/64_64/$order->itemID.png\" class='ricon'>";
              break;
        
            case 23:
              $icon = "<img src=\"".IMG_DIR."/types/structuretypes_png/64_64/$order->itemID.png\" class='ricon'>";
              break;
              
            case 22:
              $icon = "<img src=\"".IMG_DIR."/types/deployabletypes_png/64_64/$order->itemID.png\" class='ricon'>";
              break;
              
            case 40:
              $icon = "<img src=\"".IMG_DIR."/types/sovereigntystructurestypes_png/64_64/$order->itemID.png\" class='ricon'>";
              break;
              
            case 41:
              $icon = "<img src=\"".IMG_DIR."/types/planetaryinteractiontypes_png/64_64/$order->itemID.png\" class='ricon'>";
              break;
          
            default:
              if($order->iconFile)$icon = "<img src=\"".IMG_DIR."/icons/64_64/icon".$order->iconFile.".png\" class='ricon'>";
              else $icon = "<img src=\"".IMG_DIR."/icons/64_64/icon07_15.png\" class='ricon'>";
          }
          $icon = "<img src=\"".IMG_DIR."/Types/".$order->itemID."_64.png\" class='ricon'>";

          switch($order->currentStep) {
            case 0:
              $status = "<img src=\"img/level0.gif\"><i> waiting for processing ... (<a href=\"#\" onclick=\"javascript:cancelOrder($order->orderID);\">cancel?</a>)</i>";
              break;
            
            case 1:
              $status = "<img src=\"img/level1_act.gif\"><i> looking for BP ...</i>";
              break;
        
            case 2:
              $status = "<img src=\"img/level1.gif\"><i> waiting for research line</i>";
              break;
        
            case 3:
              $status = "<img src=\"img/level2_act.gif\"><i> research in progress ...</i>";
              break;
              
            case 4:
              $status = "<img src=\"img/level2.gif\"><i> research complete</i>";
              break;
              
            case 5:
              $status = "<img src=\"img/level3_act.gif\"><i> looking for materials ...</i>";
              break;
              
            case 6:
              $status = "<img src=\"img/level3.gif\"><i> waiting for production line ...</i>";
              break;
        
            case 7:
              $status = "<img src=\"img/level4_act.gif\"><i> production in progress ...</i>";
              break;
        
            case 8:
              $status = "<img src=\"img/level4.gif\"><i> production complete</i>";
              break;
              
            case 9:
              $status = "<img src=\"img/level5_act.gif\"><i> delivering ...</i>";
              break;
              
            case 10:
              $status = "<img src=\"img/level5.gif\"><i> completed</i>";
              break;

            case 99:
              $status = "<img src=\"img/level0.gif\"><i> canceled</i>";
              break;
              
          default:
              $status = "<img src=\"img/level0.gif\"><i> status unknown ?!</i>";
          }
          $cost = $order->quantity * $order->cost;
          if(!$order->assignedTo)$assignTo = "Not assigned yet";
          else {          
            
            $assignTo = "Assigned to ".$Uassign[$order->orderID]['name'];
            
          }
          if($order->corpID == $Uassign[$order->orderID]['corpid']){
            $reduc = 5/100;
            $cost = $cost - $cost*$reduc;
    }

          if($order->forCorp)$forCorp = "<i>for $order->corpName</i>";
          echo "<li id='order_$order->orderID'>$icon
          $order->quantity x $order->typeName $forCorp<br />
          $assignTo<br />
          Price : ".number_format($cost, 2, '.', ',')." ISK";if($reduc)echo " (-".($reduc*100)."%)";echo "<br />
          Status : $status<br />
          Comment : <input type='text' id='commentaire' value='' style='width:180px;' class=\"comment\" /></li>";
        }
        echo "</ul>";

        
        
        break;


        
      case 'MyCompleted':
        $myCOrderSQL = "SELECT orderID, FedMart_users.character , quantity, forCorp, FedMart_corp.corpName, cost, startTime, endTime, assignedTo, currentStep, itemID, invTypes.typeName, invGroups.categoryID, eveIcons.iconFile
                       FROM `FedMart_orders`
                       LEFT JOIN invTypes ON itemID=invTypes.typeID
                       LEFT JOIN invGroups ON invTypes.groupID=invGroups.groupID
                       LEFT JOIN eveIcons ON invTypes.iconID=eveIcons.iconID 
                       LEFT JOIN FedMart_users ON owner=characterID
                       LEFT JOIN FedMart_corp ON FedMart_users.corpID=FedMart_corp.corpID 
                       WHERE owner='$userID' AND currentStep = '10' ORDER BY endTime DESC LIMIT 10 ";
    
  
        
        mysql_select_db(DB_NAME,$db)  or die('Erreur de selection '.mysql_error());
        $req = mysql_query($myCOrderSQL) or die('Erreur SQL !<br>'.$myCOrderSQL.'<br>'.mysql_error());
        echo "<ul>";
        while ($order = mysql_fetch_object($req)){
          switch($order->categoryID) {
            case 6:
              $icon = "<img src=\"".IMG_DIR."/types/shiptypes_png/32_32/$order->itemID.png\" class='ricon'>";
              break;
            
            case 9:
              $icon = "<img src=\"".IMG_DIR."/blueprints/$order->itemID.png\" class='ricon'>";
              break;
        
            case 18:
              $icon = "<img src=\"".IMG_DIR."/types/dronetypes_png/32_32/$order->itemID.png\" class='ricon'>";
              break;
        
            case 23:
              $icon = "<img src=\"".IMG_DIR."/types/structuretypes_png/32_32/$order->itemID.png\" class='ricon'>";
              break;
              
            case 22:
              $icon = "<img src=\"".IMG_DIR."/types/deployabletypes_png/32_32/$order->itemID.png\" class='ricon'>";
              break;
              
            case 40:
              $icon = "<img src=\"".IMG_DIR."/types/sovereigntystructurestypes_png/32_32/$order->itemID.png\" class='ricon'>";
              break;
              
            case 41:
              $icon = "<img src=\"".IMG_DIR."/types/planetaryinteractiontypes_png/32_32/$order->itemID.png\" class='ricon'>";
              break;
              
            default:
              if($order->iconFile)$icon = "<img src=\"".IMG_DIR."/icons/32_32/icon".$order->iconFile.".png\" class='ricon'>";
              else $icon = "<img src=\"".IMG_DIR."/icons/32_32/icon07_15.png\" class='ricon'>";
          }
          $icon = "<img src=\"".IMG_DIR."/Types/".$order->itemID."_64.png\" class='ricon'>";

          $cost = $order->quantity * $order->cost;
          if(!$order->assignedTo)$assignTo = "Not assigned yet";
          else $assignTo = "Assigned to $order->assignedTo";
          
          echo "<li id='order_$order->orderID'>$icon
          $order->quantity x $order->typeName
          - ".number_format($cost, 2, '.', ',')." ISK - 
          ".date("d M Y",strtotime($order->endTime))."
          </li>";
        }
        echo "</ul>";
        break;

    }

  } 
}


if (isset($_POST['barPrice'])) {
  $priceQuoteSQL = "SELECT contractPrice, typeName, eveIcons.iconFile FROM `FedMart_matosAM`
                    LEFT JOIN invTypes USING (typeID)
                    LEFT JOIN eveIcons ON invTypes.iconID=eveIcons.iconID WHERE contractPrice > '0'";
        
  mysql_select_db(DB_NAME,$db)  or die('Erreur de selection '.mysql_error());
  $req = mysql_query($priceQuoteSQL) or die('Erreur SQL !<br>'.$priceQuoteSQL.'<br>'.mysql_error());
  while ($item = mysql_fetch_object($req)){
    echo "<img src=\"".utils::get_icon($item->iconFile,64)."\" width='32' alt=\"$item->typeName\" title=\"$item->typeName\"> ".number_format($item->contractPrice, 2, '.', ',')." ";
  }
  
}

if (isset($_POST['oreCCalc'])) {
  $priceQuoteSQL = "SELECT contractPrice, typeName, eveIcons.iconFile FROM `FedMart_matosAM`
                    LEFT JOIN invTypes USING (typeID)
                    LEFT JOIN eveIcons ON invTypes.iconID=eveIcons.iconID WHERE contractPrice > '0'";
        
  mysql_select_db(DB_NAME,$db)  or die('Erreur de selection '.mysql_error());
  $req = mysql_query($priceQuoteSQL) or die('Erreur SQL !<br>'.$priceQuoteSQL.'<br>'.mysql_error());
  echo "<table class=\"oreCCalc\">";
  while ($item = mysql_fetch_object($req)){
    $name = strtr($item->typeName,' ','_');
    echo "<tr><td class='ico'><img src=\"".utils::get_icon($item->iconFile,64)."\" width='32' alt=\"$name\" title=\"$name\"></td><td class='price'><span id='$name.val' >".number_format($item->contractPrice, 2, '.', '')."</span> ISK x </td><td class='quantity'><input type='text' id='$name.qty' value='0'  onchange=\"javascript:OreCalc('$name');\" /></td><td class='total'><span id='$name.tot'>0.00</span></td></tr>";
  }
  echo "<tr><td class='Gtotal' colspan='3'>Total : </td><td class='total'><span id='totalContract'>0.00 ISK</span></td></tr></table>";
}

if (isset($_POST['admin'])) {
  if($_SESSION["role"] >= 4 ){
    $admin = new admin();
    switch($_POST['admin']){
      case 'listUsers':
          $admin = new admin();
          if (isset($_POST['search'])){
            echo $admin->listAllUsers($_POST['search']);
          } else {
            echo $admin->listAllUsers();
          }
        break;

      case 'chRights':
      /*
        compo true
        t1_cap  true
        t1_mod  false
        t1_rigs true
        t1_ships  true
        t2_cap  true
        t2_mod  true
        t2_rigs true
        t2_ships  true
        t3_ships  true

        $r->roles = $t1mod&1 | 
          ($t2mod&1) << 1 | 
          ($t1rig&1) << 2 | 
          ($t2rig&1) << 3 | 
          ($t1ship&1) << 4 | 
          ($t2ship&1) << 5 | 
          ($t3ship&1) << 6 | 
          ($t1cap&1) << 7 | 
          ($t2cap&1) << 8 |
          ($compo&1) << 9; 
*/
        if(isset($_POST["charID"])){
          $charID = $_POST["charID"];
          $rightsBit =
          utils::b2n($_POST['t1_mod']) | utils::b2n($_POST['t2_mod']) << 1 |
          utils::b2n($_POST['t1_rigs']) << 2 | utils::b2n($_POST['t2_rigs']) << 3 |
          utils::b2n($_POST['t1_ships']) << 4 | utils::b2n($_POST['t2_ships']) << 5 | utils::b2n($_POST['t3_ships']) << 6 |
          utils::b2n($_POST['t1_cap']) << 7 | utils::b2n($_POST['t2_cap']) << 8 |
          utils::b2n($_POST['compo']) << 9 ;
          
          $admin->chRights($charID, $rightsBit);
        }
        break;

    }
  }
}


if (isset($_POST['getCharStat'])) {
  $charID = $_POST['getCharStat'];
  $charStat = new charStat($charID);
  //echo $charID;
 


  $fpship = "
    SELECT distinct FedMart_orders.itemID as itemID, sum(FedMart_orders.quantity) as nbitm,invTypes.typeName , invGroups.groupName
    FROM `FedMart_orders`
    INNER JOIN invTypes ON (invTypes.typeID = FedMart_orders.itemID)
    INNER JOIN invGroups ON (invGroups.groupID = invTypes.groupID)
    WHERE `assignedTo` = $charID
    AND invGroups.CategoryID = 6
    AND FedMart_orders.currentStep = 10
    group by FedMart_orders.itemID
    order by nbitm DESC, endTime DESC
    LIMIT 3
    ";

  $fpmod = "
    SELECT distinct FedMart_orders.itemID as itemID, sum(FedMart_orders.quantity) as nbitm,invTypes.typeName , invGroups.groupName
    FROM `FedMart_orders`
    INNER JOIN invTypes ON (invTypes.typeID = FedMart_orders.itemID)
    INNER JOIN invGroups ON (invGroups.groupID = invTypes.groupID)
    WHERE `assignedTo` = $charID
    AND invGroups.CategoryID IN (7,18)
    AND FedMart_orders.currentStep = 10
    group by FedMart_orders.itemID
    order by nbitm DESC, endTime DESC
    LIMIT 3
    ";

  $techspec = "
    ( SELECT sum(quantity) as total, invBlueprintTypes.techLevel
    FROM `FedMart_orders`
    inner JOIN invTypes ON itemID=invTypes.typeID
    inner JOIN invBlueprintTypes ON itemID=invBlueprintTypes.productTypeID
    inner JOIN invGroups ON invTypes.groupID=invGroups.groupID
    WHERE currentStep = '10' AND (assignedTo = $charID) 
    AND categoryID != 8
    group by techLevel)
    union
    ( SELECT sum(quantity) as total, 4
    FROM `FedMart_orders`
    inner JOIN invTypes ON itemID=invTypes.typeID
    inner JOIN invMarketGroups ON invTypes.marketGroupID=invMarketGroups.marketGroupID
    WHERE assignedTo = $charID
    AND invMarketGroups.parentGroupID IN (761,766,812,817,1047,1089)
    AND currentStep = 10
    group by assignedTo)
    ";

  $achiev = "
    select *
    from `FedMart_usersAchievements` 
    inner join `FedMart_achievements` on FedMart_achievements.id=FedMart_usersAchievements.achievID
    where userID=$charID
  ";

  mysql_select_db(DB_NAME,$db)  or die('Erreur de selection '.mysql_error());

  mysql_query("SET NAMES 'utf8'");


  $reqfpship = mysql_query($fpship) or die('Erreur SQL !<br>'.$fpship.'<br>'.mysql_error());
  $reqfpmod = mysql_query($fpmod) or die('Erreur SQL !<br>'.$fpmod.'<br>'.mysql_error());
  //$reqtechspec = mysql_query($techspec) or die('Erreur SQL !<br>'.$techspec.'<br>'.mysql_error());
  $reqachiev =  mysql_query($achiev) or die('Erreur SQL !<br>'.$achiev.'<br>'.mysql_error());

  echo $charStat->getFavShip();
  echo $charStat->getFavMod();
  echo $charStat->getExpShip();
  echo $charStat->getExpMod();
  echo $charStat->getOrderStat();


  if(mysql_num_rows($reqfpship)>0){
  echo "<div class='stat_expship'>";
  echo "<p class='stitle'>Favorite Ship built</p>";
  while ($fpship = mysql_fetch_object($reqfpship)){
    $ITicon = "<div title=\"$fpship->nbitm x $fpship->typeName\" class='typeicon' style='background-image: url(\"".IMG_DIR."/Types/".$fpship->itemID."_64.png\");'></div>";
    echo "<div class='minitem'>$ITicon</div>";
  }
  echo "</div>";
  }

  if(mysql_num_rows($reqfpmod)>0){
  echo "<div class='stat_favmod'>";
  echo "<p class='stitle'>Favorite Module built</p>";
  while ($fpmod = mysql_fetch_object($reqfpmod)){
    $ITicon = "<div title=\"$fpmod->nbitm x $fpmod->typeName\" class='typeicon' style='background-image: url(\"".IMG_DIR."/Types/".$fpmod->itemID."_64.png\");'></div>";
    echo "<div class='minitem'>$ITicon</div>";
  }
  echo "</div>";
  }

  if(mysql_num_rows($reqachiev)>0){
  echo "<div class='stat_expship'>";
  echo "<p class='stitle'>Achievements</p>";
  while ($achiev = mysql_fetch_object($reqachiev)){
    echo "<p class='achievement' title='$achiev->desc'>$achiev->name</p>";
  }
  echo "</div>";
  }

}


if (isset($_POST['stats'])) {

  $globalStat = new globalStat();

  $stats = new Template(TPLPATH."stats.tpl", array(
            'ShipList' => $globalStat->getShipList(),
            'nbShip' => $globalStat->nbShip,
            'nbOShip' => $globalStat->nbOShip,
            'ModTechList' => $globalStat->getModTechList(),
            'nbMod' => $globalStat->nbMod,
            'nbOMod' => $globalStat->nbOMod,
            'LadderC' => $globalStat->getLadderC(),
            'LadderP' => $globalStat->getLadderP(),
            'lastmonth' => date("F",mktime(0, 0, 0, date("m")-1, date("d"), date("Y"))),
            'TopBuyer' => $globalStat->getTopBuyer(),
            'TopSeller' => $globalStat->getTopSeller(),
            'GTitem' => $globalStat->nbShip + $globalStat->nbMod,
            'GTorder' => $globalStat->nbOShip + $globalStat->nbOMod,
            'InProgress' => $globalStat->getInProgress(),
            'OnHold' => $globalStat->getOnHold(),
  ));

  $stats->render();
  
}

if (isset($_POST['live'])) {


      if(!$_POST['time'])
        $_POST['time'] = 0;

    $lasttime = $_POST['time'];
    if($lasttime == 0) $lasttime = time();


  // Prod Line
    $prodRight =  $_SESSION["prodRight"];
    $limit = "(";

    $limit .= ($prodRight&1) ? "(techLevel = 1 AND categoryID IN (7,8,18) AND groupName NOT LIKE 'Rig%') OR " : "" ;
    $limit .= ($prodRight>>1&1) ? "(techLevel = 2 AND categoryID IN (7,8,18) AND groupName NOT LIKE 'Rig%') OR " : "" ;
    $limit .= ($prodRight>>2&1) ? "(techLevel = 1 AND categoryID IN (7,8,18) AND groupName LIKE 'Rig%') OR " : "" ;
    $limit .= ($prodRight>>3&1) ? "(techLevel = 2 AND categoryID IN (7,8,18) AND groupName LIKE 'Rig%') OR " : "" ;
    $limit .= ($prodRight>>4&1) ? "(techLevel = 1 AND categoryID = 6) OR " : "" ;
    $limit .= ($prodRight>>5&1) ? "(techLevel = 2 AND categoryID = 6) OR " : "" ;
    $limit .= ($prodRight>>6&1) ? "(techLevel = 3 AND categoryID = 6) OR " : "" ;
    $limit .= ($prodRight>>9&1) ? "(categoryID = 17) OR " : "" ;

    if(strlen($limit)>3) $limit = substr($limit,0,-4);
    else $limit .= "0";
    $limit .= ")";


    $userID = $_SESSION["userID"];
    $userCorp = $_SESSION["corp"];
    $plorders = array();
    mysql_select_db(DB_NAME,$db)  or die('Erreur de selection '.mysql_error());
    $plordersSQL = "SELECT FedMart_orders.orderID, FedMart_orders.cost, FedMart_orders.assignedTo, FedMart_orders.currentStep, FedMart_users.character, UNIX_TIMESTAMP(FedMart_orders.last_updated) as last_updated, FedMart_orders.corpOnly
                   
                   FROM FedMart_orders
                   LEFT JOIN FedMart_users ON FedMart_orders.assignedTo=FedMart_users.characterID
                   LEFT JOIN invTypes ON itemID=invTypes.typeID
                   LEFT JOIN invBlueprintTypes ON itemID=invBlueprintTypes.productTypeID
                   LEFT JOIN invGroups ON invTypes.groupID=invGroups.groupID
                   WHERE (FedMart_orders.assignedTo!='$userID' or FedMart_orders.assignedTo IS NULL)
                   AND NOT (FedMart_users.corpID != '$userCorp' AND FedMart_orders.corpOnly = 1)
                   AND $limit
                   AND FedMart_orders.last_updated > FROM_UNIXTIME($lasttime)";



    $req = mysql_query($plordersSQL) or die('Erreur SQL !<br>'.$plordersSQL.'<br>'.mysql_error());
    while ($order = mysql_fetch_object($req)){

  
  if(!$order->assignedTo)$assignTo = "Not assigned yet";
  else $assignTo = "Assigned to $order->character";

  $forder = new order();

  $forder->userRole = $_SESSION["role"];
  $forder->userID = $_SESSION["userID"];

      $plorders[] = array(
            "orderID" => $order->orderID,
            "status" => $forder->getStatus($order->currentStep),
            "nstat" => $order->currentStep,
            "price" => number_format($order->cost, 2, '.', ',')." ISK",
            "assignedTo" => $assignTo,
            "formated" => $forder->getOrder($order->orderID),
        );
      if($lasttime < $order->last_updated)$lasttime = $order->last_updated;
    }



  // My Orders
  $userID = $_SESSION["userID"];
  $myorders = array();
    mysql_select_db(DB_NAME,$db)  or die('Erreur de selection '.mysql_error());

        $myOrderSQL = "SELECT FedMart_orders.orderID, FedMart_orders.cost, FedMart_orders.assignedTo, FedMart_orders.currentStep
                       FROM `FedMart_orders`
                       WHERE FedMart_orders.owner='$userID'
                       AND FedMart_orders.assignedTo!='$userID'
                       AND FedMart_orders.currentStep < '10'
                       AND FedMart_orders.last_updated > FROM_UNIXTIME(".$_POST['time'].")";

        $req = mysql_query($myOrderSQL) or die('Erreur SQL !<br>'.$myOrderSQL.'<br>'.mysql_error());
        
        while ($order = mysql_fetch_array($req)){
          $myorders[] = $order;
        }
  // Shoutbox

      $shout = array();
      $arr = file('messages.txt');

      foreach($arr as $row) {
        $aTemp = null;
        list($aTemp['time'], $aTemp['nickname'], $aTemp['message']) = explode('|', $row); 
        if($aTemp['message'] AND $aTemp['time'] > $_POST['time']){
    //$aTemp['message'] = stripslashes($aTemp['message']);
          $shout[] = $aTemp;
          if($lasttime < $aTemp['time']) $lasttime = $aTemp['time'];
        }
        
      }
  

  // JSON creation
  $final = array (
          "prodline" => $plorders,
          "myorders" => $myorders,
          "shoutbox" => $shout,
          "lasttime" => $lasttime,
    );
  
  require_once('JSON.php');
        header('Content-type: text/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 01 Jul 1999 00:00:00 GMT');
header('Last-Modified: '. date("r",$lasttime));

//set_time_limit(0);

/*         header("Expires: Sat, 1 Jan 2005 00:00:00 GMT");
 Header("Cache-Control: must-revalidate");
header("Pragma: public");
 $offset = 60 * 60 * 24 * 3;
 $ExpStr = "Expires: " . gmdate("D, d M Y H:i:s", time() + $offset) . " GMT";
 Header($ExpStr);*/
  $json = new Services_JSON();
  $out = $json->encode($final);
  print $out;

}

?>
