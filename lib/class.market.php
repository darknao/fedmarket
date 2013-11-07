<?php

class market {
    
    private $db = null;
    private $item = null;
    public $corp = null;
    
    function market() {
        
    }

    public function loadCategory($catID = null){
        if (!isset($this->db)) $this->db = new eveDB();
        $block = "<ul>";
        if (!isset($catID)) {
            $sql = "
            SELECT * FROM invMarketGroups 
            LEFT JOIN iconIDs USING (iconID) 
            WHERE invMarketGroups.parentGroupID IS NULL
            AND invMarketGroups.marketGroupID != '2' 
            AND invMarketGroups.marketGroupID != '19'
            AND invMarketGroups.marketGroupID != '24'
            AND invMarketGroups.marketGroupID != '150'
            AND invMarketGroups.marketGroupID != '475'
            AND invMarketGroups.marketGroupID != '477'
            AND invMarketGroups.marketGroupID != '1320'
            AND invMarketGroups.marketGroupID != '1396'
            AND invMarketGroups.marketGroupID != '1659'
            AND invMarketGroups.marketGroupID != '350001'

            ORDER BY invMarketGroups.marketGroupName
            ";
            $block .= "<li><input type='text' id='searchMarket' value='Search ...' style='width:300px;' class='ac_input'></li>";
        }
        else {
            $catID = $this->db->real_escape_string($catID);
            $sql = "
            SELECT * FROM invMarketGroups 
            LEFT JOIN iconIDs USING (iconID) 
            WHERE invMarketGroups.parentGroupID = '$catID' 
            AND marketGroupName NOT LIKE '%Faction%'
            ORDER BY invMarketGroups.hasTypes ASC, invMarketGroups.marketGroupName ASC
            ";
        }
        
        if($result = $this->db->query($sql)) {
            while($res = $result->fetch_object()){
                if(!$res->iconFile)$icon = ICON . "items/38_16_189.png";
                else $icon = utils::get_icon($res->iconFile,64);
                $block .= "<li id=\"market_$res->marketGroupID\" class=\"group\"><a href=\"#\" onclick=\"javascript:devGroup('$res->marketGroupID');\"><img src=\"$icon\" width='16' alt='$res->marketGroupID'>$res->marketGroupName<img class=\"menuItemMoreImage\" src=\"".ICON."items/38_16_218.png\" alt='+'></a></li>";
            }
        }

        $sql = "select hastypes from invMarketGroups where marketGroupID = '$catID'";
        if($result = $this->db->query($sql)) {
            $res = $result->fetch_object();
            if($res->hastypes == 1){
                $sql = "
                SELECT a.typeID, a.typeName 
                FROM `invTypes` as a
                INNER JOIN invBlueprintTypes as b ON a.typeID=b.productTypeID
                INNER JOIN invTypes as c ON b.blueprintTypeID=c.typeID
                WHERE a.marketGroupID='$catID'
                AND a.published='1'
                AND c.published='1'
                ";
                if($result = $this->db->query($sql)) {
                    while($res = $result->fetch_object()){
                        $block .= "<li class=\"item\"><a href=\"#\" onclick=\"javascript:showInfo('$res->typeID');\">$res->typeName</a></li>";
                    }
                }
            }
        }

        $block .= "</ul>";
        return $block;
    }

    public function loadItem($item){
        $this->item = new eveItem($item);

        if (!isset($this->db)) $this->db = new eveDB();

      $batch_size = $this->item->getBatchSize();
      
      $desc = $this->item->getDesc();
      $itemID = $this->item->getID();

      $icon = "<img src=\"".$this->item->getIcon()."\" style=\"float:left;\" alt='".$this->item->getID()."'>";
      $sicon = "<img src=\"".$this->item->getIcon(32)."\" style=\"float:left;\" alt='".$this->item->getID()."'>";

        $cost = $this->item->getProdCost() / $batch_size;
        $costSell = $this->getPrice($itemID);

        
        
        if($cost>0)$costAlli = $cost + ($costSell - $cost)/2.5;
        
        if(($this->item->tech >1 && $costSell>0) || $cost == 0){$cost = $costSell * (1 - 0.10);}
        if( $this->item->tech >1 && $costSell == 0) $cost = 0;
        
        if($cost > 10000 && $cost < 1000000)$cost = utils::flooring($cost, 1000);
        elseif($cost > 1000000 && $cost < 50000000)$cost = utils::flooring($cost, 100000);
        elseif($cost > 50000000 && $cost < 300000000)$cost = utils::flooring($cost, 1000000);
        elseif($cost > 300000000)$cost = utils::flooring($cost, 10000000);

        if($costAlli > 10000 && $costAlli < 1000000)$costAlli = utils::flooring($costAlli, 1000);
        elseif($costAlli > 1000000 && $costAlli < 50000000)$costAlli = utils::flooring($costAlli, 100000);
        elseif($costAlli > 50000000 && $costAlli < 300000000)$costAlli = utils::flooring($costAlli, 1000000);
        elseif($costAlli > 300000000)$costAlli = utils::flooring($costAlli, 10000000);

        if( $this->item->tech >1 || $costAlli == 0)$costAlli = $cost;

        if($this->corp != MAIN_CORP) $cost = $costAlli;
                
        
        if($cost > 0 && $costSell>0)$reduc = ( $cost / $costSell - 1 ) * 100;
        if($reduc < 0)$typeReduc = 'goodreduc';
        else $typeReduc = 'badreduc';
        
        $pcost = number_format($cost, 2, '.', ','). " ISK";         // Your Price
        $pcostSell = number_format($costSell, 2, '.', ','). " ISK"; // Sinq Laison
        
        if($costSell ==0)$pcostSell = "n/a";
        if($cost == 0)$pcost = "n/a";
        
        $aItem = array (
            'id' => $this->item->getID(),
            'name' => $this->item->getName(),
            'desc' => $this->item->getDesc(),
            'icon' => $icon,
            'sicon' => $sicon,
            'cost' => $cost,
            'costSell' => $costSell,
            'reduc' => $reduc,
            'typereduc' => $typeReduc,
            );
        return $aItem;
        
    }

    public function getPrice($itemID) {
        if (!isset($this->db)) $this->db = new eveDB();

        $sql = "SELECT MIN(price) as price,region_id,date_created
        FROM items_selling
        WHERE region_id IN ('10000032','10000002')
        AND type_id = '$itemID'
        GROUP BY region_id
        ORDER BY region_id DESC;";

        $price = null;
        if($result = $this->db->query($sql)) {
            if($res = $result->fetch_object())
                $price = $res->price;
        }
        return $price;
    }


}

?>