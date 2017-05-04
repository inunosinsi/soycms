<?php

class LoadPage extends WebPage {
	
	function doPost(){
		if(soy2_check_token() && (isset($_POST["mode"]) && $_POST["mode"] == "load")){
			
			if(!isset($_POST["login_id"]) || !strlen($_POST["login_id"])){
				echo json_encode(array("soy2_token" => soy2_get_token(), "title" => "", "overview" => "", "content" => ""));
				exit;
			}
			
			SOY2::import("util.StepMailUtil");
			$dir = StepMailUtil::getDirectory($_POST["login_id"]);
			if(!file_exists($dir)) mkdir($dir);
			
			echo json_encode(array(
								"soy2_token" => soy2_get_token(),
								"title" => (file_exists($dir . "title.backup")) ? file_get_contents($dir . "title.backup") : "",
								"overview" => (file_exists($dir . "overview.backup")) ? file_get_contents($dir . "overview.backup") : "",
								"content" => (file_exists($dir . "content.backup")) ? file_get_contents($dir . "content.backup") : ""
							));
			exit;
		}
		
		echo json_encode(array("soy2_token" => soy2_get_token(), "title" => "", "overview" => "", "content" => ""));
		exit;
	}	
}