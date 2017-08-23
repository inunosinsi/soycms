<?php

class SaveCSSPage extends CMSWebPageBase {

	function doPost(){

		$result = $this->run("Page.SaveCSSAction");

		if($result->success()){
			echo json_encode(array(
				"result" => CMSMessageManager::get("PAGE_CSS_SAVE_SUCCESS"),
				"soy2_token" => soy2_get_token()
			));
		}else{
			echo json_encode(array(
				"result" => CMSMessageManager::get("PAGE_CSS_SAVE_FAILED"),
				"soy2_token" => soy2_get_token()
			));
		}

		exit;
	}

}
