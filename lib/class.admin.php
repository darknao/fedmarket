<?php

class admin {
    
    private $db = null;
    private $charID = null;
    private $oChar = null;
    public $stat = null;
    
    function admin() {
    }

    private function fetchUsers($search = ""){
        if (!isset($this->db)) $this->db = new eveDB();
        $id = $this->db->real_escape_string($this->charID);
        $array = array();
        $r = new stdClass();
        $stmt = $this->db->stmt_init();
        if($stmt->prepare("SELECT u.characterID, u.character, r.roleName, 
                                  p.rightsBit
                                  from FedMart_users as u
                            RIGHT JOIN FedMart_roles as r on (r.roleID = u.roles)
                            LEFT JOIN FedMart_prodRight as p on (p.characterID = u.characterID)
                            where u.character like ?
                            order by u.character"))
        {
        $val = "%$search%";
        $stmt->bind_param('s', $val);

        if($stmt->execute()) {
          $stmt->bind_result($charID, $name,$roleName, $rightsBit);
          while($stmt->fetch()){
            $r->name = $name;
            $r->roleName = $roleName;
            $r->charID = $charID;
            $r->roles = $rightsBit; 
            $array[] = clone $r;
          }
          return $array;
        } else {
          return false;
        }
      }
      else {
        die($stmt->error);
      }
    }

    public function listAllUsers($search = ""){
      $allUsers = $this->fetchUsers($search);
        $html = "";
        foreach ($allUsers as $user) {
          $html .= "<li>";
          $html .= "<img src='".utils::get_portrait($user->charID)."' title=\"$user->name\" width='16px' class='ricon'>";
          $html .= $user->name ." - ". $user->roleName;
          $html .= "<div class=\"rolesCheck\" id=\"$user->charID\"><div class=\"roleCheck\">
                        Modules TI:<input type=\"checkbox\" name=\"t1_mod\" ".($user->roles&1 ? "checked" : "" )." onclick=\"javascript:chRights(this)\" />
                        TII:<input type=\"checkbox\" name=\"t2_mod\" ".($user->roles>>1&1 ? "checked" : "" )." onclick=\"javascript:chRights(this)\" />
                        Rigs TI:<input type=\"checkbox\" name=\"t1_rigs\" ".($user->roles>>2&1 ? "checked" : "" )." onclick=\"javascript:chRights(this)\" />
                        TII:<input type=\"checkbox\" name=\"t2_rigs\" ".($user->roles>>3&1 ? "checked" : "" )." onclick=\"javascript:chRights(this)\" />
                        Ships TI:<input type=\"checkbox\" name=\"t1_ships\" ".($user->roles>>4&1 ? "checked" : "" )." onclick=\"javascript:chRights(this)\" />
                        TII:<input type=\"checkbox\" name=\"t2_ships\" ".($user->roles>>5&1 ? "checked" : "" )." onclick=\"javascript:chRights(this)\" />
                        TIII:<input type=\"checkbox\" name=\"t3_ships\" ".($user->roles>>6&1 ? "checked" : "" )." onclick=\"javascript:chRights(this)\" />
                        Cap TI:<input type=\"checkbox\" name=\"t1_cap\" ".($user->roles>>7&1 ? "checked" : "" )." onclick=\"javascript:chRights(this)\" />
                        TII:<input type=\"checkbox\" name=\"t2_cap\" ".($user->roles>>8&1 ? "checked" : "" )." onclick=\"javascript:chRights(this)\" />
                        Compo:<input type=\"checkbox\" name=\"compo\" ".($user->roles>>9&1 ? "checked" : "" )." onclick=\"javascript:chRights(this)\" />
                    </div></div>";
          $html .= "</li>";
        }
      //return print_r($allUsers, true);
      return $html;
    }

    private function chRole($charID, $role = 0){
      if (!isset($this->db)) $this->db = new eveDB();
      $stmt = $this->db->stmt_init();
      if($stmt->prepare("UPDATE FedMart_users as u
                            set u.roles = ?
                            WHERE u.characterID = ?"))
        {
          $stmt->bind_param('is', $role, $charID);
          if(!$stmt->execute())
            die($stmt->error);
        } else {
          die($stmt->error);
        }
    }

    public function chRights($charID, $rightsBit = 0){
        if (!isset($this->db)) $this->db = new eveDB();
        $array = array();
        $r = new stdClass();
        $stmt = $this->db->stmt_init();
        echo 0;
        if($stmt->prepare("SELECT u.roles
                            from FedMart_users as u
                            where u.characterID = ?"))
        {
          $stmt->bind_param('s', $charID);
          $stmt->execute();
          $stmt->bind_result($pRole);
          $stmt->store_result();
          $stmt->fetch();
          echo "($pRole)";
          if($pRole == 2 && $rightsBit == 0){
            //no build rights, so remove Builder role
            echo 'dnr';
            $this->chRole($charID, 0);

          }
          if($pRole < 2 && $rightsBit > 0){
            //build rights but not yet builder
            echo "upr";
            $this->chRole($charID, 2);
          }
          echo 1;
          // Check Existing rights
          if($stmt->prepare("SELECT p.characterID
                            from FedMart_prodRight as p
                            where p.characterID = ?"))
          {
            $stmt->bind_param('s', $charID);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->num_rows > 0){
              echo "u";
              // rights already exist, updating..

              if($stmt->prepare("UPDATE FedMart_prodRight as p
                                  SET p.rightsBit = ?
                                  where p.characterID = ?"))
              {
                $stmt->bind_param('is', $rightsBit, $charID);
                if(!$stmt->execute())
                  die($stmt->error);
              } else {
                die($stmt->error);
              }

            } else {
              // no previous rights, inserting..
              echo "i";
              if($stmt->prepare("INSERT INTO FedMart_prodRight (characterID, rightsBit)
                                  VALUES(?, ?)"))
              {
                $stmt->bind_param('si', $charID, $rightsBit);
                if(!$stmt->execute())
                  die($stmt->error);
              } else {
                die($stmt->error);
              }

            }
          } else {
            die($stmt->error);
          }


        }
        else {
          die($stmt->error);
        }
    }


}

?>
