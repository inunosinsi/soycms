<?php

class SaveTemplatePage extends WebPage{
	
	function __construct($args){
		if(soy2_check_token() && isset($_POST["template"])){
			
			$filePath = SOYSHOP_SITE_DIRECTORY . ".template/" . implode("/", $args);
			if(file_exists($filePath)){
				file_put_contents($filePath, $_POST["template"]);
				echo json_encode(array("soy2_token" => soy2_get_token(), "res" => 1));
				exit;
			}
		}
		
		echo json_encode(array("soy2_token" => soy2_get_token(), "res" => 0));
		exit;
	}
}