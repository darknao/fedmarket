<?php

class eveItem {
    private $ID = null;
    private $db = null;
    private $oItem = null;
    public $hasBP = null;
    private $icon = null;
    public $tech = null;
    private $prodCost = null;
    public $isSellable = false;

    public function __construct($item, $dontfetch = false) {
        if(!is_numeric($item)) $item = $this->lookup($item);
        $this->ID = $item;
        if (!$dontfetch) $this->fetch();

    }
    
    public function setItemID($item) {
        $this->ID = $item;
        $this->fetch();

    }

    private function fetch(){
        if (!isset($this->db)) $this->db = new eveDB();
        $factionGrp = array(
            '998', // Faction Light Missiles
            '999', // Faction Rockets
            '1000', // Faction Torpedoes
            '1001', // Faction Cruise Missiles
            '1002', // Faction Heavy Missiles
            '1003', // Faction Heavy Assault Missiles
            '1192', // Faction Auto-Targeting
            '1194', // Faction Citadel Torpedoes
            '1317', // Faction Citadel Cruise Missiles
            '1365', // Pirate Faction
            '1366', // Navy Faction
            '1370', // Navy Faction
            '1371', // Pirate Faction
            '1379', // Navy Faction
            '1380', // Pirate Faction
            '1392', // Faction Carrier
            '1631', // Faction Shuttles
            '1704' // Navy Faction
            );

        $id = $this->db->real_escape_string($this->ID);
        $sql = "SELECT invTypes.typeID, invTypes.typeName, invTypes.description, eveIcons.iconFile,
        invGroups.categoryID,invBlueprintTypes.blueprintTypeID, invTypes.portionSize, invBlueprintTypes.techLevel,
        invBlueprintTypes.wasteFactor, invTypes.marketGroupID
        FROM invTypes 
        LEFT JOIN eveIcons USING (iconID) 
        LEFT JOIN invGroups USING (groupID) 
        LEFT JOIN invBlueprintTypes ON invTypes.typeID=invBlueprintTypes.productTypeID 
        WHERE invTypes.typeID = '$id' LIMIT 1";
        if($result = $this->db->query($sql)) {
            if($res = $result->fetch_object()) {
                $this->oItem = $res;
                $this->oItem->description = str_replace(array("\r\n", "\n", "\r"),"<br />",$this->oItem->description);
                $this->hasBP = isset($res->BPtypeID);
                $this->tech = $res->techLevel;
                if (!in_array($res->marketGroupID, $factionGrp))
                    $this->isSellable = true;
            }

        }
        else throw new Exception('Unknown Item ($id)');

    }
    private function lookup($item){
        $cr = false;
        if (!isset($this->db)) $this->db = new eveDB();
        $item = $this->db->real_escape_string($item);
        $sql = "SELECT typeID FROM invTypes WHERE invTypes.typeName = '$item' LIMI 1";
        if($result = $this->db->query($sql)) {
            if($res = $result->fetch_object()) {
                $cr = $res->typeID;
            }
        } 
        return $cr;
    }

    public function getName(){
        return $this->oItem->typeName;
    }

    public function getID(){
        return $this->oItem->typeID;
    }

    public function getDesc(){
        return $this->oItem->description;
    }

    public function getIcon($size = 64){
        return IMG_DIR."/Types/".$this->oItem->typeID."_$size.png";

    }

    public function getBatchSize(){
        return $this->oItem->portionSize;
    }

    public function getProdCost(){
        
        if($this->tech == 1) {
            if (!isset($this->db)) $this->db = new eveDB();
            $sql = "(SELECT t.typeName, m.quantity, c.cost
            FROM invTypeMaterials AS m
            INNER JOIN invTypes AS t
            ON m.materialTypeID = t.typeID
            INNER JOIN FedMart_matosAM AS c
            ON m.materialTypeID = c.typeID
            WHERE m.typeID = '".$this->oItem->typeID."')
            UNION
            (SELECT t.typeName, r.quantity , MIN(sell.price) as cost
            FROM ramTypeRequirements AS r
            INNER JOIN invTypes AS t
            ON r.requiredTypeID = t.typeID
            INNER JOIN invGroups AS g
            ON t.groupID = g.groupID
            JOIN items_selling as sell
            ON r.requiredTypeID = sell.type_id
            WHERE r.typeID = '".$this->oItem->blueprintTypeID."'
            AND sell.region_id = '10000002'
            AND r.activityID = 1 
            AND g.categoryID != 16
            GROUP BY t.typeID,sell.region_id)

            ";


            if($result = $this->db->query($sql)) {
                $cost=0;
               //echo $sql;
                while($res = $result->fetch_object()){
                    $curr_rsrc = round((int)$res->quantity * ((int)$this->oItem->wasteFactor /100+1));
                    $cost += (int)$curr_rsrc * (double)$res->cost;
                    //echo " ".$res->typeName." : $curr_rsrc * ".$res->cost." \r\n";
                }
                
                $this->prodCost = $cost;
            }
        }
        return $this->prodCost;
    }

    public function getDetailledProdCost($recursion = 0){
        if (!isset($this->db)) $this->db = new eveDB();
        $recursion++;

        $totalPrice = 0;

        $strRec = str_repeat(" ", $recursion);
        $rawMat = $this->db->stmt_init();
        $rawMat = $this->db->prepare("SELECT typeid,name,greatest(0,sum(quantity)) quantity from (
              select invTypes.typeid typeid,invTypes.typeName name,quantity
              from invTypes,invTypeMaterials
              where invTypeMaterials.materialTypeID=invTypes.typeID
               and invTypeMaterials.TypeID=?
              union
              select invTypes.typeid typeid,invTypes.typeName name,
                     invTypeMaterials.quantity*r.quantity*-1 quantity
              from invTypes,invTypeMaterials,ramTypeRequirements r,invBlueprintTypes bt
              where invTypeMaterials.materialTypeID=invTypes.typeID
               and invTypeMaterials.TypeID =r.requiredTypeID
               and r.typeID = bt.blueprintTypeID
               and r.activityID = 1 and bt.productTypeID=? and r.recycle=1
            ) t where quantity>0 group by typeid,name");

        $rawMat->bind_param('ii', $this->ID, $this->ID);

        $extraMat = $this->db->prepare("SELECT t.typeName name, r.quantity quantity,
	    r.damagePerJob dmg,t.typeID typeid
            FROM ramTypeRequirements r,invTypes t,invBlueprintTypes bt,invGroups g
            where r.requiredTypeID = t.typeID and r.typeID = bt.blueprintTypeID
            and r.activityID = 1 and bt.productTypeID=? and g.categoryID != 16
            and t.groupID = g.groupID");

        $extraMat->bind_param('i', $this->ID);

        $rawMat->execute();
        $result = $rawMat->get_result();
        $hasRaw = $hasExtr = true;

        if($result->num_rows > 0){
            //printf("%s material list for %s:\r\n", $strRec, $this->oItem->typeName);
            while($res = $result->fetch_object()){
		//if($res->quantity > 0){
	          $waste = (($this->tech > 1 && $recursion==1) ? 50 : 0.1);
		  $qtywithwaste = round((int)$res->quantity * ($waste/100 +1));
                  //printf("%s - %d x %s (%.2f%% waste)\r\n", $strRec, $qtywithwaste, $res->name, $waste);
                  $interItem = new eveItem($res->typeid, true);
                  $interPrice = $interItem->getDetailledProdCost($recursion) * $qtywithwaste;
                  $totalPrice += $interPrice;
                  //printf("%s SubTotal %s\r\n", $strRec, utils::sISK($interPrice));
		//}
            }
        } else {
            $hasRaw = false;

        }
        
        $extraMat->execute();
        $result = $extraMat->get_result();
        if($result->num_rows > 0){
            //printf("%s material list for %s:\r\n", $strRec, $this->oItem->typeName);
            while($res = $result->fetch_object()){
	        //if($res->quantity > 0){
                  //printf("%s - %d x %s\r\n", $strRec, $res->quantity, $res->name);
                  $interItem = new eveItem($res->typeid, true);
                  $interPrice = $interItem->getDetailledProdCost($recursion) * $res->quantity;
                  $totalPrice += $interPrice;
                  //printf("%s SubTotal %s\r\n", $strRec, utils::sISK($interPrice));
		//}
            }
        } else {
            $hasExtr = false;
        }

        if (!$hasRaw && !$hasExtr) {
            $itemPrice = $this->getJPrice();
            //printf("%s add %s\r\n", $strRec, utils::sISK($itemPrice));
            $totalPrice += $itemPrice;
        }

        return $totalPrice;
    }

    public function getJPrice() {
        if (!isset($this->db)) $this->db = new eveDB();

        $sql = "SELECT MIN(price) as price,region_id,date_created
        FROM items_selling
        WHERE region_id = '10000002'
        AND type_id = '".$this->ID."'
        GROUP BY region_id
        ORDER BY region_id;";

        $price = null;
        if($result = $this->db->query($sql)) {
            if($res = $result->fetch_object())
                $price = $res->price;
        }
        return $price;
    }

}

?>
