<?php

class eveItem {
    private $ID = null;
    private $db = null;
    private $oItem = null;
    public $hasBP = null;
    private $icon = null;
    public $tech = null;
    private $prodCost = null;

	public function __construct($item) {
        if(!is_numeric($item)) $item = $this->lookup($item);
        $this->ID = $item;
        $this->fetch();

	}
    
    public function setItemID($item) {
        $this->ID = $item;
        $this->fetch();

    }

    private function fetch(){
        if (!isset($this->db)) $this->db = new eveDB();
        $id = $this->db->real_escape_string($this->ID);
        $sql = "SELECT invTypes.typeID, invTypes.typeName, invTypes.description, eveIcons.iconFile,
        invGroups.categoryID,invBlueprintTypes.blueprintTypeID, invTypes.portionSize, invBlueprintTypes.techLevel,
        invBlueprintTypes.wasteFactor
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

    public function getJPrice() {
        if (!isset($this->db)) $this->db = new eveDB();

        $sql = "SELECT MIN(price) as price,region_id,date_created
        FROM items_selling
        WHERE region_id = '10000002'
        AND type_id = '".$this->oItem->typeID."'
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