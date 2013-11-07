<?php

class character {
    
    private $db = null;
    private $charID = null;
    private $oChar = null;
    public $stat = null;
    
    function character($char) {
        if(!is_numeric($char)) $char = $this->lookup($char);
        $this->charID = $char;
        $this->fetchInfo();
        $this->stat = new charStat($this->charID);
    }

    private function fetchInfo(){
        if (!isset($this->db)) $this->db = new eveDB();
        $id = $this->db->real_escape_string($this->charID);
        $sql = "SELECT FedMart_users.*, FedMart_corp.corpName, FedMart_corp.corpTick, FedMart_roles.roleName
               FROM FedMart_users
               LEFT JOIN FedMart_corp USING (corpID)
               LEFT JOIN FedMart_roles ON (FedMart_users.roles=FedMart_roles.roleID)
               WHERE `characterID` = '$id'
               LIMIT 1";
        if($result = $this->db->query($sql)) {
            if($res = $result->fetch_object()) {
                $this->oChar = $res;
            }
        }
    }

    private function lookup($char){
        $cr = false;
        if (!isset($this->db)) $this->db = new eveDB();
        $char = $this->db->real_escape_string($char);
        $sql = "SELECT characterID FROM FedMart_users WHERE `character` LIKE '$char' LIMIT 1";
        if($result = $this->db->query($sql)) {
            if($res = $result->fetch_object()) {
                $cr = $res->characterID;
            }
        } 
        return $cr;
    }

    public function get_portrait($size = 256) {
      $base_url= "http://image.eveonline.com/Character";
      $cached_file = "cache/portrait/".$this->oChar->characterID."_".$size.".jpg";
      if (!file_exists($cached_file)){
        $param = $this->oChar->characterID."_".$size.".jpg";
        $portrait = imagecreatefromjpeg($base_url."/".$param);
        imagejpeg($portrait,$cached_file);
        imagedestroy($portrait);
      }
      return "lib/".$cached_file;
    }

    public function getName(){
        return $this->oChar->character;
    }

    public function getID(){
        return $this->oChar->characterID;
    }

    public function getSumInfoXML(){
      Header("content-type: application/xml");
      $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
      $xml .= "<character>";
      if(is_object($this->oChar)){
        $xml .= "
          <id>".$this->oChar->characterID."</id>
          <name>".$this->oChar->character."</name>
          <role>".$this->oChar->roles."</role>
          <roleName>".$this->oChar->roleName."</roleName>
          <portrait>".$this->get_portrait(512)."</portrait>
          <corpName>".$this->oChar->corpName."</corpName>
          <corpTick>".$this->oChar->corpTick."</corpTick>
        ";
      }
      $xml .= "</character>";

      echo $xml;
    }

    public function getFullInfoXML(){
      Header("content-type: application/xml");
      $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
      $xml .= "<character>";
      if(is_object($this->oChar)){
        $this->stat->getOrderStat();

        $xml .= "
          <id>".$this->oChar->characterID."</id>
          <name>".$this->oChar->character."</name>
          <role>".$this->oChar->roles."</role>
          <roleName>".$this->oChar->roleName."</roleName>
          <portrait>".$this->get_portrait(512)."</portrait>
          <corpName>".$this->oChar->corpName."</corpName>
          <corpTick>".$this->oChar->corpTick."</corpTick>";

        $xml .= "
            <totalSpent>".number_format($this->stat->CtotalISK, 2, '.', ',')." ISK</totalSpent>
            <totalOrder>".(int)$this->stat->CtotalOrd."</totalOrder>
            <buyrank>".$this->stat->Crank."</buyrank>";
        
        if($this->oChar->roles >= 2 ){
            $xml .= "
                <totalWin>".number_format($this->stat->PtotalISK, 2, '.', ',')." ISK</totalWin>
                <totalCOrder>".$this->stat->PtotalOrd."</totalCOrder>
                <prdrank>".(int)$this->stat->Prank."</prdrank>";
        }
      }
      $xml .= "</character>";

      echo $xml;
    }

}

?>
