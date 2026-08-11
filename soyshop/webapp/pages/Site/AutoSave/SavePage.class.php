<?php

class SavePage extends WebPage {

	function doPost(){
		if(soy2_check_token() && (isset($_POST["mode"]) && $_POST["mode"] == "auto_save")){
			if(!isset($_POST["login_id"]) || !strlen($_POST["login_id"])){
				echo json_encode(array("soy2_token" => soy2_get_token(), "result" => 0));
				exit;
			}

			$backupDir = (isset($_POST["dir"])) ? $_POST["dir"] : "entry";
			$dir = SOYSHOP_SITE_DIRECTORY . "." . $backupDir . "/";
			if(!file_exists($dir)){
				mkdir($dir);
				file_put_contents($dir . ".htaccess", "deny from all");
			}
			$dir .= $_POST["login_id"] . "/";
			if(!file_exists($dir)) mkdir($dir);

			$flag = false;	//実行フラグ

			if(isset($_POST["title"]) && strlen($_POST["title"])){
				file_put_contents($dir . "title.backup", $_POST["title"]);
				$flag = true;
			}

			if(isset($_POST["content"]) && strlen(strip_tags($_POST["content"]))){
				file_put_contents($dir . "content.backup", self::convert($_POST["content"]));
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

	private function convert($content){
		$content = str_replace("</p><p>", "</p>\n<p>", $content);
		$content = str_replace("<br></p>", "</p>", $content);
		return str_replace("<br data-mce-bogus=\"1\"></p>", "</p>", $content);
	}
}
?>
