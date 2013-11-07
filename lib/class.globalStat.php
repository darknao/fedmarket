<?php

class globalStat {
    
    private $db = null;
    private $graph = null;
    private $onHold = null;
    private $inProgress = null;
    public $nbShip = null;
    public $nbOShip = null;
    public $nbMod = null;
    public $nbOMod = null;
    
    function globalStat() {
        $this->graph = new BAR_GRAPH("hBar");
        $this->graph->showValues = 1;
        $this->graph->barWidth = 10;
        $this->graph->barLength = 1.0;
        $this->graph->labelSize = 12;
        $this->graph->absValuesSize = 12;
        $this->graph->percValuesSize = 12;
        $this->graph->graphPadding = 0;
        $this->graph->graphBGColor = "";
        $this->graph->graphBorder = "0px solid blue";
        $this->graph->barColors = "#ffffff";
        $this->graph->barBGColor = "";
        $this->graph->barBorder = "1px outset white";
        $this->graph->labelColor = "#ffffff";
        $this->graph->labelBGColor = "";
        $this->graph->labelBorder = "0px groove white";
        $this->graph->absValuesColor = "#ffffff";
        $this->graph->absValuesBGColor = "";
        $this->graph->absValuesBorder = "0px groove white";
        $this->graph->percValuesColor = "#ffffff";
        $this->graph->labelFont = "Evefont,Helvetica";
    }

    public function getLadderC(){
        if (!isset($this->db)) $this->db = new eveDB();
          $sql = "
          SELECT distinct assignedTo, count(orderID) as nborder, FedMart_users.character,sum(quantity*cost) as total, FedMart_rank.rankname
          FROM `FedMart_orders`
          INNER JOIN FedMart_users ON (FedMart_orders.assignedTo=FedMart_users.characterID)
          LEFT JOIN FedMart_rank ON (FedMart_users.prod_rank=FedMart_rank.rankid)
          WHERE
          currentStep = 10
          GROUP By assignedTo
          ORDER by nborder DESC
          LIMIT 10
         ";
         $block = "";
        if($result = $this->db->query($sql)) {
            while($res = $result->fetch_object()){
                $block .= "<li>";
                $portrait = "<div class='rportrait' style=\"background-image: url('".utils::get_portrait($res->assignedTo,32)."');\" title=\"$res->character\"></div>";
                $total = number_format($res->nborder, 0, ' ', ','). " orders";
                $block .= "$portrait <a href='#!/charstat/$res->character'>$res->character</a><br/><i>$res->rankname</i><br/><b>$total</b><br/>";
            }
        }
        return $block;
    }

    public function getLadderP() {
        if (!isset($this->db)) $this->db = new eveDB();
            $sql = "
            SELECT distinct owner, count(orderID) as nborder, FedMart_users.character,sum(quantity*cost) as total, FedMart_rank.rankname
            FROM `FedMart_orders`
            INNER JOIN FedMart_users ON (FedMart_orders.owner=FedMart_users.characterID)
            LEFT JOIN FedMart_rank ON (FedMart_users.buyer_rank=FedMart_rank.rankid)
            WHERE
            currentStep = 10
            GROUP By owner
            ORDER by total DESC
            LIMIT 10
            ";
         $block = "";
        if($result = $this->db->query($sql)) {
            while($res = $result->fetch_object()){
                $block .= "<li>";
                $portrait = "<div class='rportrait' style=\"background-image: url('".utils::get_portrait($res->owner,32)."');\" title=\"$res->character\"></div>";
                $total = number_format($res->total, 2, '.', ','). " ISK";
                $block .= "$portrait <a href='#!/charstat/$res->character'>$res->character</a><br/><i>$res->rankname</i><br/><b>$total</b><br/>";
            }
        }
        return $block;            
    }

    public function getTopBuyer(){
        if (!isset($this->db)) $this->db = new eveDB();
        $sql = "
        SELECT distinct owner, FedMart_users.character,sum(quantity*cost) as total
        FROM `FedMart_orders`
        INNER JOIN FedMart_users ON (FedMart_orders.owner=FedMart_users.characterID)
        WHERE `endTime` > date_format(now()-interval 1 month,'%Y-%m')
        AND endTime != date(0)
        AND currentStep = 10
        GROUP By owner
        ORDER by total DESC
        LIMIT 3
        ";
        $block = "";
        $top = 1;
        if($result = $this->db->query($sql)) {
            while($res = $result->fetch_object()){
                if($top == 1)$portrait = "<img src='".utils::get_portrait($res->owner)."' title=\"$res->character\" width='128px' class='ricon'>";
                else
                    $portrait = "<img src='".utils::get_portrait($res->owner)."' title=\"$res->character\" width='64px' class='ricon'>";
                $total = number_format($res->total, 2, '.', ','). " ISK";
                $block .= "$portrait<br/>$res->character<br/>$total<br/>";
                $top++;

            }
        }
        return $block;
    }

    public function getTopSeller(){
        if (!isset($this->db)) $this->db = new eveDB();
        $sql = "
        SELECT distinct assignedTo, count(orderID) as nborder, FedMart_users.character,sum(quantity*cost) as total
        FROM `FedMart_orders`
        INNER JOIN FedMart_users ON (FedMart_orders.assignedTo=FedMart_users.characterID)
        WHERE `endTime` > date_format(now()-interval 1 month,'%Y-%m')
        AND endTime != date(0)
        AND currentStep = 10
        GROUP By assignedTo
        ORDER by nborder DESC
        LIMIT 3
        ";
        $block = "";
        $top = 1;
        if($result = $this->db->query($sql)) {
            while($res = $result->fetch_object()){
                if($top == 1)$portrait = "<img src='".utils::get_portrait($res->assignedTo)."' title=\"$res->character\" width='128px' class='ricon'>";
                else
                    $portrait = "<img src='".utils::get_portrait($res->assignedTo)."' title=\"$res->character\" width='64px' class='ricon'>";
                $total = number_format($res->nborder, 0, ' ', ','). " Orders";
                $block .= "$portrait<br/>$res->character<br/>$total<br/>";
                $top++;

            }
        }
        return $block;
    }

    public function getShipList(){
        if (!isset($this->db)) $this->db = new eveDB();
            $sql = "
            SELECT distinct invGroups.groupName as type, count(invGroups.groupName) as nbOrd, sum(FedMart_orders.quantity) as nb 
            FROM `FedMart_orders`
            LEFT JOIN invTypes ON (invTypes.typeID = FedMart_orders.itemID)
            LEFT JOIN invGroups ON (invGroups.groupID = invTypes.groupID)
            WHERE invGroups.CategoryID = 6 and FedMart_orders.currentStep = 10
            group by invGroups.groupName
            ";
            $block = "";
            $this->flushGraphLabel();
            if($result = $this->db->query($sql)) {
                while ($ship = $result->fetch_object()){
                    $this->nbShip += $ship->nb;
                    $this->nbOShip += $ship->nbOrd;
                    $this->graph->labels[] = $ship->type;
                    $this->graph->values[] = $ship->nb;
                }
            }
        return $this->graph->create();        
    }

    public function getModTechList() {
        if (!isset($this->db)) $this->db = new eveDB();
            $sql = "
            select distinct  count(invTypes.typeID) as nbOrd, sum(FedMart_orders.quantity) as nb, IFNULL(invMetaTypes.metaGroupID,1) as tech 
            FROM `FedMart_orders`
            LEFT JOIN invTypes ON (invTypes.typeID = FedMart_orders.itemID)
            LEFT JOIN invGroups ON (invGroups.groupID = invTypes.groupID)
            LEFT JOIN invMetaTypes ON (invTypes.typeID = invMetaTypes.typeID)
            WHERE invGroups.CategoryID IN (7,18) and FedMart_orders.currentStep = 10
            group by invMetaTypes.metaGroupID
            ";
            $block = "";
            $this->flushGraphLabel();
            if($result = $this->db->query($sql)) {
                while ($mod = $result->fetch_object()){
                    $this->nbMod += $mod->nb;
                    $this->nbOMod += $mod->nbOrd;
                    $this->graph->labels[] = "Tech $mod->tech";
                    $this->graph->values[] = $mod->nb;
                }
            }
        return $this->graph->create();         
    }

    public function getInProgress(){
        if(!isset($this->inProgress)) $this->getPendingOrder();
        return $this->inProgress;
    }

    public function getOnHold(){
        if(!isset($this->onHold)) $this->getPendingOrder();
        return $this->onHold;
    }

    private function getPendingOrder(){
        if (!isset($this->db)) $this->db = new eveDB();
        $sql = "
        select (sELECT count(`orderID`) as nbOrderP FROM `FedMart_orders` 
        WHERE  currentStep < 10 and currentStep > 0) as progress,
        (select count(`orderID`) as nbOrderP FROM `FedMart_orders` 
        WHERE currentStep = 0) as hold
        ";
        if($result = $this->db->query($sql)) {
            $order = $result->fetch_object();
            $this->inProgress = $order->progress;
            $this->onHold = $order->hold;
        }
    }

    private function flushGraphLabel() {
        $this->graph->labels = array();
        $this->graph->values = array();
    }
}

?>