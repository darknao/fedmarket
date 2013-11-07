<?php
  session_start();

  function replace(&$item, $key) {
    $item = str_replace('|', '-', $item);
  }
  
  if (!function_exists('file_put_contents')) {
		function file_put_contents($fileName, $data) {
			if (is_array($data)) {
				$data = join('', $data);
			}
			$res = @fopen($fileName, 'w+b');
			if ($res) {
				$write = @fwrite($res, utf8_encode($data));
				if($write === false) {
					return false;
				} else {
					return $write;
				}
			}
		}
	}
  
  //file_put_contents('debug.txt', print_r($_GET, true));
  if (!$_SESSION["logged_in"])die();
  $nickname = $_SESSION["userName"];

  switch($_GET['action']) {
    case 'add':
      array_walk($_POST, 'replace');
      $arr = file('messages.txt');
      
      if(count($arr) > 10)
        array_shift($arr);
      
      $_POST['nickname'] = htmlentities($nickname,ENT_COMPAT,"UTF-8");
      $_POST['message'] = htmlentities($_POST['message'],ENT_COMPAT,"UTF-8");
      $time = time();
      $arr[] = $time.'|'.$_POST['nickname'].'|'.$_POST['message'].'|'.$_SERVER['REMOTE_ADDR']."\n";
      file_put_contents('messages.txt', implode('', $arr));

      $data['response'] = 'Good work';
      $data['nickname'] = $_POST['nickname'];
      $data['message'] = stripslashes($_POST['message']);
      $data['time'] = $time;
    break;
    
    case 'view':
      $data = array();
      $arr = file('messages.txt');
      if(!$_GET['time'])
        $_GET['time'] = 0;
      foreach($arr as $row) {
        $aTemp = null;
        list($aTemp['time'], $aTemp['nickname'], $aTemp['message']) = explode('|', $row); 
        if($aTemp['message'] AND $aTemp['time'] > $_GET['time']){
	  //$aTemp['message'] = stripslashes($aTemp['message']);
          $data[] = $aTemp;
	  }
      }
      //file_put_contents('debug.txt', print_r($data, true));
    break;
  }
  
  require_once('JSON.php');
      	header('Content-type: text/json; charset=utf-8');
  $json = new Services_JSON();
  $out = $json->encode($data);
  print $out;
?>
