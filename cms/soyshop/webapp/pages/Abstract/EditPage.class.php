<?php

class EditPage extends WebPage{

	function doPost(){
		if(soy2_check_token()){
			SOYShop_DataSets::put("soyshop_abstract", trim($_POST["content"]));
			SOY2PageController::jump("Abstract.Edit?mode=free");
		}
	}

	function __construct(){
		parent::__construct();

		//権限ないアカウントは概要の編集画面は開けないが念の為に機能を封じておく
		DisplayPlugin::toggle("button", AUTH_ABSTRACT);
		DisplayPlugin::toggle("edit", AUTH_ABSTRACT);
		DisplayPlugin::toggle("script", AUTH_ABSTRACT);

		$content = SOYShop_DataSets::get("soyshop_abstract", "");

		$this->addForm("form");

		$this->addTextArea("content", array(
			"name" => "content",
			"value" => $content,
			"style" => "height:320px;"
		));
	}
}
