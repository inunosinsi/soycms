<?php

class LoadPage extends WebPage {

	function doPost(){
		if(soy2_check_token() && (isset($_POST["mode"]) && $_POST["mode"] == "load")){

			if(!isset($_POST["login_id"]) || !strlen($_POST["login_id"])){
				echo json_encode(array("soy2_token" => soy2_get_token(), "title" => "", "content" => ""));
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

			echo json_encode(array(
								"soy2_token" => soy2_get_token(),
								"title" => (file_exists($dir . "title.backup")) ? file_get_contents($dir . "title.backup") : "",
								"content" => (file_exists($dir . "content.backup")) ? file_get_contents($dir . "content.backup") : ""
							));
			exit;
		}

		echo json_encode(array("soy2_token" => soy2_get_token(), "title" => "", "content" => ""));
		exit;
	}
}
