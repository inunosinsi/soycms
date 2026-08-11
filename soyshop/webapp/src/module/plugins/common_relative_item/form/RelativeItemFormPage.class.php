<?php

class RelativeItemFormPage extends WebPage{

	private $itemId;
	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.common_relative_item.util.RelativeItemUtil");
		SOY2::import("module.plugins.common_relative_item.component.RelativeItemListComponent");
	}

	function execute(){
		parent::__construct();

		$codes = RelativeItemUtil::getCodesByItemId($this->itemId);

		$this->createAdd("relative_item_list", "RelativeItemListComponent", array(
			"list" => array_unique($codes)
		));

		$this->addSelect("relative_item_select", array(
			"options" => self::_buildRelativeItemSelect($codes)
		));
	}

	private function _buildRelativeItemSelect(array $codes){
		$dao = new SOY2DAO();
		try{
			$results = $dao->executeQuery("SELECT item_name, item_code, item_type FROM soyshop_item WHERE is_disabled != 1");
		}catch(Exception $e){
			$results = array();
		}
		unset($dao);
		if(!count($results)) return array();

		$opts = array();
		foreach($results as $res){
			if(is_numeric($res["item_type"]) || !strlen($res["item_code"])) continue;
			if(!in_array($res["item_code"], $codes)){
				$opts[$res["item_code"]] = $res["item_code"] . " : " . $res["item_name"];
			}
		}
		unset($results);
		return $opts;
	}

	function setItemId($itemId){
		$this->itemId = $itemId;
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
