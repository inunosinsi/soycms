<?php

class DetailPage extends WebPage{
		
	function doPost(){
		
		if(soy2_check_token()){
			
		}
	}
	
	/**
	 * $args[0]にURLの末尾のIDが入る
	 */
	function __construct($args){

		WebPage::__construct();


//		$this->createAdd("form", "HTMLForm", array(
//			"method" => "post",
//			"action" => SOY2PageController::createLink(APPLICATION_ID . ".Sample.Detail")
//		));

		//上記のコメントアウトのタグと同じ機能を持つタグ
		$this->createAdd("form", "HTMLForm");
		
		//名前
		$this->createAdd("name", "HTMLInput", array(
			"name" => "name",
			"value" => (isset($_POST["name"])) ? $_POST["name"] : null
		));
		
		//説明
		$this->createAdd("description", "HTMLTextArea", array(
			"name" => "description",
			"value" => (isset($_POST["description"])) ? $_POST["description"] : null
		));
		
		$this->createAdd("male", "HTMLCheckBox", array(
			"name" => "sex",
			"value" => "m",
			"selected" => (!isset($_POST["sex"]) || (isset($_POST["sex"]) && $_POST["sex"] == "m")),
			"label" => "男性"
		));
		
		$this->createAdd("female", "HTMLCheckBox", array(
			"name" => "sex",
			"value" => "f",
			"selected" => (isset($_POST["sex"]) && $_POST["sex"] == "f"),
			"label" => "女性"
		));
		
		$this->createAdd("check", "HTMLCheckBox", array(
			"name" => "check",
			"value" => 1,
			"selected" => (isset($_POST["check"]) && $_POST["check"] == 1),
			"label" => "有"
		));
		
		$this->createAdd("select", "HTMLSelect", array(
			"name" => "select",
			"options" => array("hoge", "huga", "mock", "soy"),
			"selected" => (isset($_POST["select"])) ? $_POST["select"] : null
		));
	}
}
?>