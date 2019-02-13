<?php

class IndexPage extends CMSWebPageBase {

	function doPost(){
		if(soy2_check_token() && isset($_POST["label_id"]) && is_numeric($_POST["label_id"])){
			$res = SOY2Logic::createInstance("site_include.plugin.CustomField.logic.EntryFieldLogic")->getEntriesByLabelId($_POST["label_id"]);
			
			if(!count($res)){
				echo json_encode(array("soy2_token" => soy2_get_token(), "result" => 0));
				exit;
			}

			echo json_encode(array("soy2_token" => soy2_get_token(), "result" => 1, "list" => $res));
			exit;
		}
	}
}
