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
			"selected" => (soyshop_get_item_attribute_value($this->itemId, FbCatalogManagerUtil::FIELD_ID_EXHIBITATION, "int") === 1),
			"label" => "出品する"
		));

		//カテゴリを読み込む
		$values = self::_getTaxonomyFirstValues((int)$this->itemId);
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

		$cfg = FbCatalogManagerUtil::getConfig();

		$itemCfg = soy2_unserialize(soyshop_get_item_attribute_value($this->itemId, FbCatalogManagerUtil::FIELD_ID_ITEM_INFO, "string"));

		//画像
		$this->createAdd("fb_catalog_image","_common.Item.ImageSelectComponent", array(
			"domId" => "fb_catalog_image",
			"name" => "FbCatalogManager[" . FbCatalogManagerUtil::FIELD_ID_ITEM_INFO . "][image]",
			"value" => (isset($itemCfg["image"]) && is_string($itemCfg["image"]) && strlen($itemCfg["image"])) ? soyshop_convert_file_path_on_admin($itemCfg["image"]) : ""
		));

		$brand = (isset($itemCfg["brand"])) ? $itemCfg["brand"] : null;
		if(is_null($brand) && isset($cfg["brand"])) $brand = $cfg["brand"];
		$this->addInput("brand", array(
			"name" => "FbCatalogManager[" . FbCatalogManagerUtil::FIELD_ID_ITEM_INFO . "][brand]",
			"value" => $brand
		));

		$this->addSelect("condition", array(
			"name" => "FbCatalogManager[" . FbCatalogManagerUtil::FIELD_ID_ITEM_INFO . "][condition]",
			"options" => FbCatalogManagerUtil::getConditionList(),
			"selected" => (isset($itemCfg["condition"])) ? $itemCfg["condition"] : false
		));

		$this->addInput("shipping_price", array(
			"name" => "FbCatalogManager[" . FbCatalogManagerUtil::FIELD_ID_ITEM_INFO . "][shippingPrice]",
			"value" => self::_getShippingPrice($itemCfg, $cfg),
			"style" => "width:80px;"
		));

		$this->addTextArea("shop_description", array(
			"name" => "FbCatalogManager[" . FbCatalogManagerUtil::FIELD_ID_ITEM_INFO . "][shopDescription]",
			"value" => (isset($itemCfg["shopDescription"])) ? $itemCfg["shopDescription"] : ""
		));

		$this->addInput("ajax_path", array(
			"value" => rtrim(SOY2PageController::createLink(""), "/") . "/index.php?facebook_catalog_manager",
		));

		$this->addLabel("taxonomy_js", array(
			"html" => "\n" . file_get_contents(dirname(dirname(__FILE__)) . "/js/taxonomy.js")
		));
	}

	// array("first" => "", "second" => "", "third" => "", "fourth" => "")
	private function _getTaxonomyFirstValues(int $itemId){
		return soy2_unserialize(soyshop_get_item_attribute_value($itemId, FbCatalogManagerUtil::FIELD_ID_TAXONOMY, "string"));
	}

	private function _getShippingPrice($itemCfg, $cfg){
		if(isset($itemCfg["shippingPrice"]) && is_numeric($itemCfg["shippingPrice"])) return $itemCfg["shippingPrice"];

		return (isset($cfg["shippingPrice"])) ? (int)$cfg["shippingPrice"] : 0;
	}


	function setItemId($itemId){
		$this->itemId = $itemId;
	}
}
