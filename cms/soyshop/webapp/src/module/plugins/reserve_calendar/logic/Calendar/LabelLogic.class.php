<?php

class LabelLogic extends SOY2LogicBase{
	
	function __construct(){
		SOY2::import("module.plugins.reserve_calendar.domain.SOYShopReserveCalendar_Label");
		SOY2::import("module.plugins.reserve_calendar.domain.SOYShopReserveCalendar_LabelDAO");
	}
	
	function getLabelList($itemId = null){
		if(isset($itemId) && is_numeric($itemId)){
			$labels = self::getLabelsByItemId($itemId);
		}else{
			try{
				$labels = self::dao()->get();
			}catch(Exception $e){
				return array();
			}
		}
		
		
		if(!count($labels)) return array();
		
		$list = array();
		foreach($labels as $label){
			$list[$label->getId()] = $label->getLabel();
		}
		
		return $list;
	}
	
	function getLabelListAll(){
		try{
			$labels = self::dao()->get();
		}catch(Exception $e){
			return array();
		}
		
		if(!count($labels)) return array();
		
		$list = array();
		
		//商品ID毎に集める array(itemId => array(id => label))
		foreach($labels as $label){
			$list[$label->getItemId()][$label->getId()] = $label->getLabel();
		}
		
		return $list;
	}
	
	function getLabelsByItemId($itemId){
		try{
			return self::dao()->getByItemId($itemId);
		}catch(Exception $e){
			return array();
		}
	}
	
	function getLabelNameById($labelId){
		try{
			return self::dao()->getById($labelId)->getLabel();
		}catch(Exception $e){
			return "";
		}
	}
	
	function getRegisteredItemsOnLabel(){
		$list = self::getRegisteredItemIdsOnLabel();
		
		$dao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		$now = time();
		$sql = "SELECT id, item_name FROM soyshop_item ".
				"WHERE id IN (" . implode(",", $list) . ") ".
				"AND item_is_open = " . SOYShop_Item::IS_OPEN . " ".
				"AND open_period_start < " . $now . " ".
				"AND open_period_end > " . $now; 
				
		try{
			$res = $dao->executeQuery($sql);
		}catch(Exception $e){
			return array();
		}
		
		if(!count($res)) return array();
		
		$list = array();
		foreach($res as $v){
			$list[$v["id"]] = $v["item_name"];
		}
		
		return $list;
	}
	
	function getRegisteredItemIdsOnLabel(){
		return self::dao()->registerdItemIdsOnLabel();
	}
	
	
	function dao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("SOYShopReserveCalendar_LabelDAO");
		return $dao;
	}
}
?>