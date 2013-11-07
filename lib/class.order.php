<?php
require_once("class.eveDB.php");

class order {
    
    private $db = null;
    private $args;
    //public $userID;
    public $userRole = 0;

    function order() {
        
    }

    public function __get($name) {
        return $this->args[$name];
    }

    public function create($args = array()){
        if (!isset($this->db)) $this->db = new eveDB();
        $this->args = $args;

        $MorderID = $this->insMainOrder();
        $valueSQL = "VALUES";

        for ($i=0;$i<$this->nbitem;$i++){
            $itemID = $this->items[$i];
            $qty = $this->quantity[$i];
            $cost = $this->price[$i];
            $valueSQL .= "('".$this->userID."','".$this->forCorp."','".$this->corpOnly."', '".$this->items[$i]."', '".$this->quantity[$i]."', '".$this->price[$i]."', NOW(), '$MorderID'),";

        }
        $valueSQL = trim($valueSQL,',');


        $sql = "INSERT INTO FedMart_orders (`owner`,`forCorp`,`corpOnly`,`itemID`,`quantity`,`cost`,`startTime`, `MorderID`)
                    $valueSQL";
            //    echo $sql;
        return $this->db->query($sql);
    }

    public function createAPI($userID, $cart, $corpOnly = 0, $forCorp = 0){
        if (!isset($this->db)) $this->db = new eveDB();

        $this->forCorp = $forCorp;
        $this->corpOnly = $corpOnly;
        $this->userID = $userID;

        $MorderID = $this->insMainOrder();
        $valueSQL = "VALUES";

        foreach($cart as $item){
            $itemID = $item['itemID'];
            $qty = $item['qty'];
            $cost = $item['cost'];
            $valueSQL .= "('".$userID."','".$this->forCorp."','".$this->corpOnly."', '".$itemID."', '".$qty."', '".$cost."', NOW(), '$MorderID'),";
        }
        $valueSQL = trim($valueSQL,',');

        $sql = "INSERT INTO FedMart_orders (`owner`,`forCorp`,`corpOnly`,`itemID`,`quantity`,`cost`,`startTime`, `MorderID`)
                    $valueSQL";
        if($this->db->query($sql)) return $MorderID;
        else throw new Exception("Order NOT completed !");
    }

    public function getOrder($orderID) {
        if (!isset($this->db)) $this->db = new eveDB();
        $sql = "SELECT orderID, MorderID, FedMart_users.character ,FedMart_users.characterID ,FedMart_users.corpID, FedMart_corp.corpName, FedMart_corp.corpTick , quantity, cost, startTime, endTime, assignedTo, currentStep,forCorp, invTypes.typeName, invTypes.typeID, invBlueprintTypes.techLevel,invBlueprintTypes.blueprintTypeID, invGroups.categoryID, a.character as acharacter, a.corpID as acorpID
        FROM `FedMart_orders`
        LEFT JOIN invTypes ON itemID=invTypes.typeID
        LEFT JOIN invBlueprintTypes ON itemID=invBlueprintTypes.productTypeID
        LEFT JOIN FedMart_users ON owner=characterID
        LEFT JOIN FedMart_corp ON FedMart_users.corpID=FedMart_corp.corpID
        LEFT JOIN invGroups ON invTypes.groupID=invGroups.groupID
        LEFT JOIN FedMart_users as a ON a.characterID=FedMart_orders.assignedTo
        WHERE FedMart_orders.orderID = '$orderID'
        LIMIT 1";


        if($result = $this->db->query($sql)) {
            $order = $result->fetch_object();

            $ITicon = "<div title=\"$order->typeName\" 
            class='rportrait' 
            style='background-image: url(\"".IMG_DIR."/Types/".$order->typeID."_64.png\");'>
            </div>";

            $icon = "<div title=\"$order->character\" class='rportrait' style='background-image: url(\"".utils::get_portrait($order->characterID,64)."\");'></div>";
            if($order->forCorp)$icon = "<div style='background-image: url(\"".utils::get_logo($order->corpID,64)."\");' title=\"$order->character for $order->corpName\" class='rportrait'></div>";

            $cost = $order->quantity * $order->cost;
            $reduc = 0;
            if(!$order->assignedTo){
                $assignTo = "Not assigned yet";
                $takeAssign = "<a href=\"#\" onclick='javascript:TakeAssign($order->orderID);'>take assignment</a>";
            }
            else {
                $assignTo = "Assigned to ".$order->acharacter;
                $takeAssign = "";
                if($order->assignedTo == $this->userID)
                    $takeAssign = "<a href=\"#\" onclick='javascript:ReleaseAssign($order->orderID);'>release assignment</a>";

                if($order->corpID == $order->acorpID){
                    $reduc = 5/100;
                    $cost = $cost - $cost*$reduc;
                } 
            }
            if($this->inEVE) $itemName = "<a href=\"#\" onclick=\"CCPEVE.showInfo($order->blueprintTypeID)\">$order->typeName</a>";
            else $itemName = "<a href=\"#\" onclick=\"javascript:showInfo($order->typeID);go('Market');\">$order->typeName</a>";

            $block = "<li id='order_$order->orderID' ";
            if(!$order->assignedTo) $block .= "class='unassigned'";
            if($order->assignedTo != $this->userID) $block .= "class='assignOther'";
            $block .= " >$icon $ITicon
            $order->quantity x $itemName<span class='action'>";
            if($order->assignedTo == $this->userID || $this->userRole == 5) $block .= "<a href=\"#\" onclick='javascript:cancelOrder($order->orderID);'>cancel order</a>";
            $block .= "</span><br />
            <span id='assigned'>$assignTo<span class='action'>$takeAssign</span></span><br />
            Price : <span id=\"price\">".number_format($cost, 2, '.', ',')." ISK";if($reduc)$block .= " (-".($reduc*100)."%)";$block .= "</span><span class='action'>"; if($order->assignedTo == $this->userID || $this->userRole == 5)$block .= "<a href=\"#\" onclick='javascript:editOPrice($order->orderID);'>edit price</a>"; $block .= "</span><br />
            Status : <span id='status'>".$this->getStatus($order->currentStep)."<span class='action'>"; if($order->assignedTo == $this->userID)$block .= $Astep; $block .= "</span></span><br />
            [$order->corpTick] $order->character";if($order->forCorp)$block .= " for $order->corpName";
            $block .= "<span class='action'>order n°$order->MorderID</span></li>";


        }

        return $block;
    }

    public function getStatus($step){

        switch($step) {
        case 0:
          $status = "<img src=\"img/level0.gif\"><i> waiting for processing ...</i>";
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
            
        return $status;
    }

    private function insMainOrder(){
        if (!isset($this->db)) $this->db = new eveDB();
        $sql = "INSERT INTO FedMart_Morders (`owner`,`forCorp`,`corpOnly`)
                    VALUES('$this->userID','$this->forCorp','$this->corpOnly')";
        $this->db->query($sql);

        return $this->db->insert_id;       
    }

}

?>