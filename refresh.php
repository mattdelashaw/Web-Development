<?php
	require_once('someFile.php');
	$dev=secretFunction($_SERVER['SERVER_ADDR'], $_SERVER['REQUEST_URI']);
	require_once($_SERVER['DOCUMENT_ROOT']."$dev/youDontNeedToKnow.php");
	
	if(isset($_POST['cookie'])){
		$cookie = preg_replace("/PHPSESSID=/", "", $_POST['cookie']);
		$now = time();
		$infoArr['logins'] = array();
		$sess = session_save_path()."/";
		if(is_dir($sess)){
			if ($dh = opendir($sess)){
				while (($file = readdir($dh)) !== false){
					if(preg_match("/$cookie/", $file)){
						$cookie = $file;
						$file = $sess.$file;
						$read = fopen($file, "r");
						$detail = fstat($read);
						if($detail[7]>0){								//file size
							$encode = file_get_contents($file);
							$broad = explode("|", $encode);
							if($broad[0]=="user"){
								echo json_encode($detail[9]);			//get timestamp for javascript accuracy
							}
						}
					fclose($read);
					}
				}
			closedir($dh);
			}
		}	
		exit;	
	}
	
	$now = time();
	$infoArr['logins'] = array();
	$sess = session_save_path()."/";
	if(is_dir($sess)){
		if ($dh = opendir($sess)){
			while (($file = readdir($dh)) !== false){
				if(preg_match("/sess/", $file)){
					$cookie = preg_replace("/sess_/", "", $file);
					$file = $sess.$file;
					$read = fopen($file, "r");
					$detail = fstat($read);
					if($detail[7]>0){								//file size
						$encode = file_get_contents($file);
						$broad = explode("|", $encode);
						if($broad[0]=="user"){
							$userName = explode('"', $broad[2]);
							$userID = explode('"', $broad[1]);
							$timeDiff = $now - $detail[9];
							$minutes = $timeDiff / 60;
							if($minutes >= 20){						//file[mtime]
								$active = 0;
							}else{
								$active = 1;
							}
							$infoArr['logins'][$detail[9]] = array("logged_in"=>$active, "user_id"=>$userID[3], "username"=>$userName[1], "logged_time"=>$detail[9], "file"=>$file, "cookie"=>$cookie);
						}
					}
				fclose($read);
				}
			}
			closedir($dh);
		}
	}
	krsort($infoArr['logins']);
	
	foreach($infoArr['logins'] as $arr => $info){
		if($info['cookie'] == $_COOKIE['PHPSESSID']){
			echo "session refreshed";
		}
	}
?>
