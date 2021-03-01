<?php

class MPFConfirmAndChoiceConfigPage extends WebPage {

	private $pluginObj;
	private $hash;

	function __construct(){
		SOY2::import("site_include.plugin.multiple_page_form.component.ChoiceItemListComponent");
		SOY2::import("site_include.plugin.multiple_page_form.util.MultiplePageFormUtil");
	}

	function doPost(){
		if(soy2_check_token()){
			$cnf = MultiplePageFormUtil::readJson($this->hash);

			$items = array();	//array(array("item" => "", "type" => ""))
			$types = array();
			foreach($_POST["Config"]["Item"] as $idx => $item){
				$item = trim($item);
				if(!strlen($item)) continue;

				$next = (isset($_POST["Config"]["Next"][$idx])) ? $_POST["Config"]["Next"][$idx] : "";
				$displayOrder = (isset($_POST["Config"]["Order"][$idx]) && is_numeric($_POST["Config"]["Order"][$idx])) ? (int)$_POST["Config"]["Order"][$idx] : count($items) + 1;

				$items[] = array("item" => $item, "next" => $next, "order" => "$displayOrder");
			}

			$cnf["choice"] = $items;
			$cnf["name"] = (isset($_POST["Config"]["name"])) ? $_POST["Config"]["name"] : "";
			$cnf["description"] = (isset($_POST["Config"]["description"])) ? $_POST["Config"]["description"] : "";
			$cnf["label"] = (isset($_POST["Config"]["label"])) ? $_POST["Config"]["label"] : "";
			$cnf["template"] = (isset($_POST["Config"]["template"])) ? $_POST["Config"]["template"] : "default";

			MultiplePageFormUtil::savePageConfig($this->hash, $cnf);

			CMSPlugin::redirectConfigPage();
		}
	}

	function execute(){
		parent::__construct();

		self::_buildConfigForm();
	}

	private function _buildConfigForm(){
		$cnf = MultiplePageFormUtil::readJson($this->hash);

		$this->addForm("form");

		$this->addInput("page_name", array(
			"name" => "Config[name]",
			"value" => MultiplePageFormUtil::getPageName($this->hash),
			"attr:required" => "required"
		));

		$this->addLabel("page_type", array(
			"text" => MultiplePageFormUtil::getTypeText($cnf["type"])
		));

		$this->addTextArea("page_description", array(
			"name" => "Config[description]",
			"value" => (isset($cnf["description"])) ? $cnf["description"] : ""
		));

		$this->addInput("label", array(
			"name" => "Config[label]",
			"value" => (isset($cnf["label"])) ? $cnf["label"] : ""
		));

		$list = MultiplePageFormUtil::getPageItemList($this->hash);

		//項目の設定があれば一覧を出力する
		$this->createAdd("choice_item_list", "ChoiceItemListComponent", array(
			"list" => (isset($cnf["choice"]) && is_array($cnf["choice"]) && count($cnf["choice"])) ? MultiplePageFormUtil::sortItems($cnf["choice"]) : array(),
			"pages" => $list
		));

		$this->addInput("add_item", array(
			"name" => "Config[Item][]",
			"value" => ""
		));

		$this->addInput("add_order", array(
			"name" => "Config[Order][]",
			"value" => ""
		));

		$this->addSelect("add_page_type", array(
			"name" => "Config[Next][]",
			"options" => $list
		));

		$this->addSelect("page_template", array(
			"name" => "Config[template]",
			"options" => MultiplePageFormUtil::getTemplateList($cnf["type"]),
			"selected" => (isset($cnf["template"])) ? $cnf["template"] : null
		));

		$this->addLabel("template_dir", array(
			"text" => MultiplePageFormUtil::getCustomTemplateFileDir($cnf["type"])
		));

		$this->addLabel("default_template_file_path", array(
			"text" => MultiplePageFormUtil::getDefaultTemplateFilePath($cnf["type"])
		));
	}

	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}

	function setHash($hash){
		$this->hash = $hash;
	}
}
