<?php

class MPFFormConfigPage extends WebPage {

	private $pluginObj;
	private $hash;

	function __construct(){
		SOY2::import("site_include.plugin.multiple_page_form.component.FormItemListComponent");
		SOY2::import("site_include.plugin.multiple_page_form.util.MultiplePageFormUtil");
		SOY2::import("site_include.plugin.multiple_page_form.util.MPFTypeFormUtil");
	}

	function doPost(){
		if(soy2_check_token()){
			if(isset($_POST["Item"])){
				$cnf = MultiplePageFormUtil::readJson($this->hash);
				$items = (isset($cnf["item"])) ? $cnf["item"] : array();
				$item = array("name" => $_POST["Item"]["name"], "type" => $_POST["Item"]["type"], "order" => count($items) + 1, "option" => "", "required" => 0);
				$items[] = $item;
				$cnf["item"] = $items;
				MultiplePageFormUtil::savePageConfig($this->hash, $cnf);
			}

			if(isset($_POST["Config"])){
				$cnf = MultiplePageFormUtil::readJson($this->hash);
				$items = (isset($cnf["item"]) && is_array($cnf["item"]) && count($cnf["item"])) ? MultiplePageFormUtil::sortItems($cnf["item"]) : array();

				foreach($_POST["Config"]["Order"] as $idx => $order){
					$items[$idx]["order"] = (is_numeric($order)) ? (int)$order : 999;
					$items[$idx]["required"] = (isset($_POST["Config"]["Required"][$idx]) && (int)$_POST["Config"]["Required"][$idx] === 1) ? 1 : 0;

					//type属性
					if(isset($_POST["Config"]["InputType"][$idx])){
						$items[$idx]["inputType"] = trim($_POST["Config"]["InputType"][$idx]);
					}

					//属性値
					if(isset($_POST["Config"]["Attribute"][$idx])){
						$items[$idx]["attribute"] = trim($_POST["Config"]["Attribute"][$idx]);
					}

					//置換文字列
					if(isset($_POST["Config"]["Replacement"][$idx])){
						$items[$idx]["replacement"] = trim($_POST["Config"]["Replacement"][$idx]);
					}

					// オプション
					if(isset($_POST["Config"]["Option"][$idx])){
						$items[$idx]["option"] = trim($_POST["Config"]["Option"][$idx]);
					}
				}

				$cnf["item"] = $items;
				$cnf["name"] = (isset($_POST["Config"]["name"])) ? $_POST["Config"]["name"] : "";
				$cnf["next"] = (isset($_POST["Config"]["next"])) ? $_POST["Config"]["next"] : "";
				$cnf["description"] = (isset($_POST["Config"]["description"])) ? $_POST["Config"]["description"] : "";
				$cnf["template"] = (isset($_POST["Config"]["template"])) ? $_POST["Config"]["template"] : "default";

				MultiplePageFormUtil::savePageConfig($this->hash, $cnf);
			}

			CMSPlugin::redirectConfigPage();
		}
	}

	function execute(){
		if(isset($_GET["remove"]) && is_numeric($_GET["remove"]) && soy2_check_token()){
			MPFTypeFormUtil::removeItem($this->hash, $_GET["remove"], MultiplePageFormUtil::readJson($this->hash));
			CMSPlugin::redirectConfigPage();
		}

		parent::__construct();

		self::_buildConfigForm();
		self::_buildAddItemForm();
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

		//項目の設定があれば一覧を出力する
		$items = (isset($cnf["item"]) && is_array($cnf["item"]) && count($cnf["item"])) ? MultiplePageFormUtil::sortItems($cnf["item"]) : array();
		DisplayPlugin::toggle("form", count($items));
		$this->createAdd("form_item_list", "FormItemListComponent", array(
			"list" => $items,
			"hash" => $this->hash
		));

		$this->addInput("item_count_hidden", array(
			"value" => count($items),
			"attr:id" => "item_count"
		));

		$this->addSelect("next_page_type", array(
			"name" => "Config[next]",
			"options" => MultiplePageFormUtil::getPageItemList($this->hash),
			"selected" => (isset($cnf["next"])) ? $cnf["next"] : ""
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

	private function _buildAddItemForm(){
		$this->addForm("add_form");

		$this->addInput("item_name", array(
			"name" => "Item[name]",
			"value" => "",
			"attr:required" => "required"
		));

		$this->addSelect("item_type", array(
			"name" => "Item[type]",
			"options" => MPFTypeFormUtil::getTypeList(),
			"attr:required" => "required"
		));
	}

	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}

	function setHash($hash){
		$this->hash = $hash;
	}
}
