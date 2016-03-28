<?php

class SaveEntryPage extends CMSWebPageBase {
	
	function doPost(){
		if(soy2_check_token() && (isset($_POST["mode"]) && $_POST["mode"] == "auto_save")){
			
			if(!isset($_POST["login_id"]) || !strlen($_POST["login_id"])){
				echo json_encode(array("soy2_token" => soy2_get_token(), "result" => 0));
				exit;
			}
			
			SOY2::import("site_include.plugin.auto_save_entry.util.AutoSaveEntryUtil");
			$dir = AutoSaveEntryUtil::getDirectory($_POST["login_id"]);
			if(!file_exists($dir)) mkdir($dir);
			
			$flag = false;	//実行フラグ
			
			if(isset($_POST["title"]) && strlen($_POST["title"])){
				file_put_contents($dir . "title.backup", $_POST["title"]);
				$flag = true;
			}
			
			if(isset($_POST["content"]) && strlen(strip_tags($_POST["content"]))){
				file_put_contents($dir . "content.backup", self::convert($_POST["content"]));
				$flag = true;
				
				if(isset($_POST["more"]) && strlen(strip_tags($_POST["more"]))){
					file_put_contents($dir . "more.backup", self::convert($_POST["more"]));
				}
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
	
	private function convert($content){
		$content = str_replace("</p><p>", "</p>\n<p>", $content);
		$content = str_replace("<br></p>", "</p>", $content);
		return str_replace("<br data-mce-bogus=\"1\"></p>", "</p>", $content);
	}
}
?>