<?php

class UpdateItemAreaPage extends WebPage{

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.arrival_update_item.util.ArrivalUpdateItemUtil");
	}

	function execute(){
		parent::__construct();

		$this->addLabel("item_count", array(
			"text" => self::_getLimit()
		));

		$this->createAdd("item_list", "_common.Item.ItemListComponent", array(
			"list" => self::_getItemList(),
			"detailLink" => SOY2PageController::createLink("Item.Detail.")
		));
	}

	//更新していない商品はなくす
	private function _getItemList(){
		$items = self::_get();
		if(!count($items)) return array();

		$list = array();
		foreach($items as $item){
			if(!is_numeric($item->getUpdateDate())) continue;
			$list[] = $item;
		}
		return $list;
	}

	private function _get(){
		$dao = soyshop_get_hash_table_dao("item");
		$dao->setLimit(self::_getLimit());
		try{
			return $dao->newItems();
		}catch(Exception $e){
			return array();
		}
	}

	private function _getLimit(){
		static $lim;
		if(is_null($lim)){
			$cnf = ArrivalUpdateItemUtil::getConfig();
			$lim = (isset($cnf["count"]) && is_numeric($cnf["count"])) ? (int)$cnf["count"] : 5;
		}
		return $lim;
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
