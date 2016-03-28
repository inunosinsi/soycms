<?php

class LoadEntryPage extends CMSWebPageBase {
	
	function doPost(){
		if(soy2_check_token() && (isset($_POST["mode"]) && $_POST["mode"] == "load")){
			
			if(!isset($_POST["login_id"]) || !strlen($_POST["login_id"])){
				echo json_encode(array("soy2_token" => soy2_get_token(), "title" => "", "content" => "", "more" => ""));
				exit;
			}
			
			SOY2::import("site_include.plugin.auto_save_entry.util.AutoSaveEntryUtil");
			$dir = AutoSaveEntryUtil::getDirectory($_POST["login_id"]);
			if(!file_exists($dir)) mkdir($dir);
			
			echo json_encode(array(
								"soy2_token" => soy2_get_token(),
								"title" => (file_exists($dir . "title.backup")) ? file_get_contents($dir . "title.backup") : "",
								"content" => (file_exists($dir . "content.backup")) ? file_get_contents($dir . "content.backup") : "",
								"more" => (file_exists($dir . "more.backup")) ? file_get_contents($dir . "more.backup") : ""
							));
			exit;
		}
		
		echo json_encode(array("soy2_token" => soy2_get_token(), "title" => "", "content" => "", "more" => ""));
		exit;
	}	
}