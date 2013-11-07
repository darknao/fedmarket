<?php

class charStat {
    
    private $db = null;
    private $charID = null;
    
    function charStat($charID) {
        $this->charID = $charID;
    }

    public function getFavShip(){
        if (!isset($this->db)) $this->db = new eveDB();
        $sql = "
        SELECT distinct FedMart_orders.itemID as itemID, sum(FedMart_orders.quantity) as nbitm,invTypes.typeName , invGroups.groupName
        FROM `FedMart_orders`
        INNER JOIN invTypes ON (invTypes.typeID = FedMart_orders.itemID)
        INNER JOIN invGroups ON (invGroups.groupID = invTypes.groupID)
        WHERE `owner` = $this->charID
        AND invGroups.CategoryID = 6
        AND FedMart_orders.currentStep = 10
        AND FedMart_orders.forCorp = 0
        group by FedMart_orders.itemID
        order by nbitm DESC, endTime DESC
        LIMIT 3
         ";
        $block = "";
        if($result = $this->db->query($sql)) {
            if($result->num_rows > 0){
                $block .= "<div class='stat_favship'>";
                $block .= "<p class='stitle'>Favorite Ship</p>";
                while($res = $result->fetch_object()){
                    $ITicon = "<div title=\"$res->nbitm x $res->typeName\" class='typeicon' style='background-image: url(\"".IMG_DIR."/Types/".$res->itemID."_64.png\");'></div>";
                    $block .= "<div class='minitem'>$ITicon</div>";
                }
                $block .= "</div>";
            }
        }
        return $block;
    }

    public function getFavMod(){
        if (!isset($this->db)) $this->db = new eveDB();
        $sql = "
        SELECT distinct FedMart_orders.itemID as itemID, sum(FedMart_orders.quantity) as nbitm,invTypes.typeName , invGroups.groupName
        FROM `FedMart_orders`
        INNER JOIN invTypes ON (invTypes.typeID = FedMart_orders.itemID)
        INNER JOIN invGroups ON (invGroups.groupID = invTypes.groupID)
        WHERE `owner` = $this->charID
        AND invGroups.CategoryID IN (7,18)
        AND FedMart_orders.currentStep = 10
        AND FedMart_orders.forCorp = 0
        group by FedMart_orders.itemID
        order by nbitm DESC, endTime DESC
        LIMIT 3
         ";
        $block = "";
        if($result = $this->db->query($sql)) {
            if($result->num_rows > 0){
                $block .= "<div class='stat_favmod'>";
                $block .= "<p class='stitle'>Favorite Module</p>";
                while($res = $result->fetch_object()){
                    $ITicon = "<div title=\"$res->nbitm x $res->typeName\" class='typeicon' style='background-image: url(\"".IMG_DIR."/Types/".$res->itemID."_64.png\");'></div>";
                    $block .= "<div class='minitem'>$ITicon</div>";
                }
                $block .= "</div>";
            }
        }
        return $block;
    }

    public function getExpShip(){
        if (!isset($this->db)) $this->db = new eveDB();
        $sql = "
        SELECT FedMart_orders.itemID , FedMart_orders.itemID ,invTypes.typeName , invGroups.groupName, FedMart_orders.cost
        FROM `FedMart_orders`
        INNER JOIN invTypes ON (invTypes.typeID = FedMart_orders.itemID)
        INNER JOIN invGroups ON (invGroups.groupID = invTypes.groupID)
        WHERE `owner` = $this->charID
        AND invGroups.CategoryID = 6
        AND FedMart_orders.currentStep = 10
        AND FedMart_orders.forCorp = 0
        group by FedMart_orders.itemID
        order by cost DESC
        limit 3
         ";
        $block = "";
        if($result = $this->db->query($sql)) {
            if($result->num_rows > 0){
                $block .= "<div class='stat_expship'>";
                $block .= "<p class='stitle'>Most expensive Ship</p>";
                while($res = $result->fetch_object()){
                    $ITicon = "<div title=\"$res->typeName\" class='typeicon' style='background-image: url(\"".IMG_DIR."/Types/".$res->itemID."_64.png\");'></div>";
                    $block .= "<div class='minitem'>$ITicon</div>";
                }
                $block .= "</div>";
            }
        }
        return $block;
    }

    public function getExpMod(){
        if (!isset($this->db)) $this->db = new eveDB();
        $sql = "
        SELECT FedMart_orders.itemID , FedMart_orders.itemID ,invTypes.typeName , invGroups.groupName, FedMart_orders.cost
        FROM `FedMart_orders`
        INNER JOIN invTypes ON (invTypes.typeID = FedMart_orders.itemID)
        INNER JOIN invGroups ON (invGroups.groupID = invTypes.groupID)
        WHERE `owner` = $this->charID
        AND invGroups.CategoryID IN (7,18)
        AND FedMart_orders.currentStep = 10
        AND FedMart_orders.forCorp = 0
        group by FedMart_orders.itemID
        order by cost DESC
        limit 3
         ";
        $block = "";
        if($result = $this->db->query($sql)) {
            if($result->num_rows > 0){
                $block .= "<div class='stat_favmod'>";
                $block .= "<p class='stitle'>Most expensive Module</p>";
                while($res = $result->fetch_object()){
                    $ITicon = "<div title=\"$res->typeName\" class='typeicon' style='background-image: url(\"".IMG_DIR."/Types/".$res->itemID."_64.png\");'></div>";
                    $block .= "<div class='minitem'>$ITicon</div>";
                }
                $block .= "</div>";
            }
        }
        return $block;
    }

    public function getOrderStat(){
        if (!isset($this->db)) $this->db = new eveDB();
        $sql = "
            select distinct count(distinct MorderID) as nborder, FedMart_users.character,sum(quantity*cost) as total, FedMart_rank.rankname,
                 
            (
                SELECT distinct sum(FedMart_orders.cost*FedMart_orders.quantity) as total
                from FedMart_orders
                where FedMart_orders.owner = '".$this->charID."'
                and FedMart_orders.MorderID > 0
                group by FedMart_orders.MorderID
                order by total DESC
                limit 1
            ) as cborder
            FROM `FedMart_orders`
            INNER JOIN FedMart_users ON (FedMart_orders.owner=FedMart_users.characterID)
            LEFT JOIN FedMart_rank ON (FedMart_users.buyer_rank=FedMart_rank.rankid)
            
            WHERE
            currentStep = 10 AND
            owner = '".$this->charID."'
            GROUP By owner
            ORDER by total DESC
        ";
        $block = "";
        if($result = $this->db->query($sql)) {
            if($result->num_rows > 0){
                $res = $result->fetch_object();
                $this->CBorder = $res->cborder;
                $this->Crank = $res->rankname;
                $this->CtotalISK = $res->total;
                $this->CtotalOrd = $res->nborder;

                
                $block .= "<div class='stat_expship'>";
                $block .= "Most expensive order : ".number_format($res->cborder, 2, '.', ',')." ISK<br />";
                $block .= "Buyer rank : $res->rankname<br />";
                $block .= "Total isk spent : ".number_format($res->total, 2, '.', ','). " ISK (".(int)$res->nborder." orders)";
                $block .= "</div>";
            }
        }

        $sql = "
                SELECT distinct assignedTo, count(orderID) as nborder, sum(quantity*cost) as total, FedMart_rank.rankname
                FROM `FedMart_orders`
                INNER JOIN FedMart_users ON (FedMart_orders.assignedTo=FedMart_users.characterID)
                LEFT JOIN FedMart_rank ON (FedMart_users.prod_rank=FedMart_rank.rankid)
                WHERE
                currentStep = 10
                and assignedTo = '".$this->charID."'
                GROUP By assignedTo 
                ORDER by total DESC
        ";
        if($result = $this->db->query($sql)) {
            if($result->num_rows > 0){
                $res = $result->fetch_object();
                
                $this->Prank = $res->rankname;
                $this->PtotalISK = $res->total;
                $this->PtotalOrd = $res->nborder;
                
            }
        }

        return $block;
    }

}

?>
