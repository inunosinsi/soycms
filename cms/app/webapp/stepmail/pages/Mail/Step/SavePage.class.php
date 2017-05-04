<?php

class SavePage extends WebPage {
	
	function doPost(){
		if(soy2_check_token() && (isset($_POST["mode"]) && $_POST["mode"] == "auto_save")){
			
			if(!isset($_POST["login_id"]) || !strlen($_POST["login_id"])){
				echo json_encode(array("soy2_token" => soy2_get_token(), "result" => 0));
				exit;
			}
			
			SOY2::import("util.StepMailUtil");
			$dir = StepMailUtil::getDirectory($_POST["login_id"]);
			if(!file_exists($dir)) mkdir($dir);
			
			$flag = false;	//実行フラグ
			
			if(isset($_POST["title"]) && strlen($_POST["title"])){
				file_put_contents($dir . "title.backup", $_POST["title"]);
				$flag = true;
			}
			
			if(isset($_POST["overview"]) && strlen($_POST["overview"])){
				file_put_contents($dir . "overview.backup", $_POST["overview"]);
				$flag = true;
			}
			
			if(isset($_POST["content"]) && strlen($_POST["content"])){
				file_put_contents($dir . "content.backup", $_POST["content"]);
				$flag = true;
			}
			
			//soy2_check_tokenの書き換え
			if($flag){
				echo json_encode(array("soy2_token" => soy2_get_token(), "result" => 1));
				exit;
			}	
		}
		
		echo json_encode(array("soy2_token" => soy2_get_token(), "result" => 0));
		exit;
	}
}
?>