<?php

class FbCatalogCustomfieldFormPage extends WebPage {

	private $itemId;

	function __construct(){
		SOY2::import("module.plugins.facebook_catalog_manager.util.FbCatalogManagerUtil");
	}

	function execute(){
		parent::__construct();

		$tanLogic = SOY2Logic::createInstance("module.plugins.facebook_catalog_manager.logic.TaxonomyLogic");

		$this->addCheckBox("exhibition", array(
			"name" => "FbCatalogManager[" . FbCatalogManagerUtil::FIELD_ID_EXHIBITATION . "]",
			"value" => 1,
			"selected" => (FbCatalogManagerUtil::get($this->itemId, FbCatalogManagerUtil::FIELD_ID_EXHIBITATION)->getValue() == 1),
			"label" => "出品する"
		));

		//カテゴリを読み込む
		$values = self::_getTaxonomyFirstValues($this->itemId);
		$this->addSelect("taxonomy_first", array(
			"name" => "FbCatalogManager[" . FbCatalogManagerUtil::FIELD_ID_TAXONOMY . "][first]",
			"options" => $tanLogic->getTaxonomy(),
			"selected" => (isset($values["first"])) ? $values["first"] : false
		));

		foreach(array("second", "third", "fourth") as $idx){
			$this->addInput("taxonomy_" . $idx . "_value", array(
				"value" => (isset($values[$idx])) ? $values[$idx] : "",
				"id" => "taxonomy_" . $idx . "_value"
			));
		}

		$cnf = FbCatalogManagerUtil::getConfig();

		$v = FbCatalogManagerUtil::get($this->itemId, FbCatalogManagerUtil::FIELD_ID_ITEM_INFO)->getValue();
		$itemCnf = (strlen($v)) ? soy2_unserialize($v) : array();

		//画像
		$this->createAdd("fb_catalog_image","_common.Item.ImageSelectComponent", array(
			"domId" => "fb_catalog_image",
			"name" => "FbCatalogManager[" . FbCatalogManagerUtil::FIELD_ID_ITEM_INFO . "][image]",
			"value" => (isset($itemCnf["image"]) && strlen($itemCnf["image"])) ? soyshop_convert_file_path_on_admin($itemCnf["image"]) : ""
		));

		$brand = (isset($itemCnf["brand"])) ? $itemCnf["brand"] : null;
		if(is_null($brand) && isset($cnf["brand"])) $brand = $cnf["brand"];
		$this->addInput("brand", array(
			"name" => "FbCatalogManager[" . FbCatalogManagerUtil::FIELD_ID_ITEM_INFO . "][brand]",
			"value" => $brand
		));

		$this->addSelect("condition", array(
			"name" => "FbCatalogManager[" . FbCatalogManagerUtil::FIELD_ID_ITEM_INFO . "][condition]",
			"options" => FbCatalogManagerUtil::getConditionList(),
			"selected" => (isset($itemCnf["condition"])) ? $itemCnf["condition"] : false
		));

		$this->addInput("shipping_price", array(
			"name" => "FbCatalogManager[" . FbCatalogManagerUtil::FIELD_ID_ITEM_INFO . "][shippingPrice]",
			"value" => self::_getShippingPrice($itemCnf, $cnf),
			"style" => "width:80px;"
		));

		$this->addTextArea("shop_description", array(
			"name" => "FbCatalogManager[" . FbCatalogManagerUtil::FIELD_ID_ITEM_INFO . "][shopDescription]",
			"value" => (isset($itemCnf["shopDescription"])) ? $itemCnf["shopDescription"] : ""
		));

		$this->addInput("ajax_path", array(
			"value" => rtrim(SOY2PageController::createLink(""), "/") . "/index.php?facebook_catalog_manager",
		));

		$this->addLabel("taxonomy_js", array(
			"html" => "\n" . file_get_contents(dirname(dirname(__FILE__)) . "/js/taxonomy.js")
		));
	}

	// array("first" => "", "second" => "", "third" => "", "fourth" => "")
	private function _getTaxonomyFirstValues($itemId){
		$value = FbCatalogManagerUtil::get($this->itemId, FbCatalogManagerUtil::FIELD_ID_TAXONOMY)->getValue();
		return (strlen($value)) ? soy2_unserialize($value) : array();
	}

	private function _getShippingPrice($itemCnf, $cnf){
		if(isset($itemCnf["shippingPrice"]) && is_numeric($itemCnf["shippingPrice"])) return $itemCnf["shippingPrice"];

		return (isset($cnf["shippingPrice"])) ? (int)$cnf["shippingPrice"] : 0;
	}


	function setItemId($itemId){
		$this->itemId = $itemId;
	}
}
