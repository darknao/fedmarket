<?php

class eveDB extends mysqli {
	public function __construct() {
        
		parent::__construct(DB_HOST, DB_USER, DB_PASS, DB_NAME);

		if(mysqli_connect_error()) {
			die('Connect Error (' . mysqli_connect_errno() . ') ' . mysqli_connect_error());
		}
        parent::query("SET NAMES 'utf8'");
	}
    

}

?>
