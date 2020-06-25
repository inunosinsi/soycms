<?php

class MultiplePageFormConfigPage extends WebPage {

	private $pluginObj;

	function __construct(){
		SOY2::import("site_include.plugin.multiple_page_form.util.MultiplePageFormUtil");
		SOY2::import("site_include.plugin.multiple_page_form.component.PageListComponent");
	}

	function doPost(){
		if(soy2_check_token()){
			if(isset($_POST["Add"])){	//ページの追加
				MultiplePageFormUtil::generateJson($_POST["Add"]["name"], $_POST["Add"]["type"]);
				CMSPlugin::redirectConfigPage();
			}

			if(isset($_POST["update"]) && isset($_POST["Display"])){	//並び順の変更
				if(is_array($_POST["Display"]) && count($_POST["Display"])){
					foreach($_POST["Display"] as $hash => $displayOrder){
						$cnf = MultiplePageFormUtil::readJson($hash);
						$cnf["order"] = (int)$displayOrder;
						MultiplePageFormUtil::savePageConfig($hash, $cnf);
					}
				}
				CMSPlugin::redirectConfigPage();
			}
		}
	}

	function execute(){
		if(isset($_GET["remove"]) && soy2_check_token()){
			MultiplePageFormUtil::removeJson($_GET["remove"]);
			SOY2PageController::jump("Plugin.Config?multiple_page_form");
		}

		parent::__construct();

		self::_buildPageListArea();
		self::_buildAddForm();
	}

	private function _buildPageListArea(){
		$isPage = MultiplePageFormUtil::isPage();
		DisplayPlugin::toggle("page", $isPage);

		$this->addForm("form");

		$this->createAdd("page_list", "PageListComponent", array(
			"list" => ($isPage) ? MultiplePageFormUtil::getPageList() : array()
		));
	}

	private function _buildAddForm(){
		$this->addForm("add_form");

		$this->addInput("page_name", array(
			"name" => "Add[name]",
			"value" => "",
			"attr:required" => "required"
		));

		$this->addSelect("page_type", array(
			"name" => "Add[type]",
			"options" => MultiplePageFormUtil::getTypeList(),
			"attr:required" => "required"
		));
	}

	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}
}
