<?php

class SupplierManagerUtil {

	public static function getParameter($key){
		$session = SOY2ActionSession::getUserSession();
		if(isset($_GET[$key])){
			$session->setAttribute("supplier_manager_search:" . $key, $_GET[$key]);
			$params = $_GET[$key];
		}else if(isset($_GET["reset"])){
			$session->setAttribute("supplier_manager_search:" . $key, array());
			$params = array();
		}else{
			$params = $session->getAttribute("supplier_manager_search:" . $key);
			if(is_null($params)) $params = array();
		}

		return $params;
	}

	public static function saveSupplierEachItemId($supplierId, $itemId){
		//最初に必ず削除
		try{
			self::_relationDao()->deleteByItemId($itemId);
		}catch(Exception $e){
			//
		}

		if(is_numeric($supplierId) && $supplierId > 0){
			$obj = new SOYShop_SupplierRelation();
			$obj->setSupplierId($supplierId);
			$obj->setItemId($itemId);
			try{
				self::_relationDao()->insert($obj);
			}catch(Exception $e){
				var_dump($e);
			}
		}
	}

	public static function getSupplierIdByItemId($itemId){
		return self::_getSupplierIdByItemId($itemId);
	}

	public static function getSupplierById($supplierId){
		static $list;
		if(is_null($list)) $list = SOY2Logic::createInstance("module.plugins.supplier_manager.logic.SupplierLogic")->getSupplierList();
		return (isset($list[$supplierId])) ? $list[$supplierId] : "";
	}

	public static function getSupplierByItemId($itemId){
		static $list;
		if(is_null($list)) $list = SOY2Logic::createInstance("module.plugins.supplier_manager.logic.SupplierLogic")->getSupplierList();
		$supplierId = self::_getSupplierIdByItemId($itemId);
		return (isset($list[$supplierId])) ? $list[$supplierId] : "";
	}

	private static function _getSupplierIdByItemId($itemId){
		try{
			return (int)self::_relationDao()->getByItemId($itemId)->getSupplierId();
		}catch(Exception $e){
			return null;
		}
	}

	public static function getItemIdsBySupplierId($supplierId){
		static $itemIds;
		if(is_null($itemIds)) $itemIds = array();
		if(!isset($itemIds[$supplierId])) $itemIds[$supplierId] = self::_getItemIdsBySupplierId($supplierId);
	 	return $itemIds[$supplierId];
	}

	private static function _getItemIdsBySupplierId($supplierId){
		try{
			$relations = self::_relationDao()->getBySupplierId($supplierId);
			if(!is_array($relations) || !count($relations)) return array();
		}catch(Exception $e){
			return array();
		}

		$itemIds = array();
		foreach($relations as $rel){
			if(is_numeric($rel->getItemId())) $itemIds[] = (int)$rel->getItemId();
		}
		return $itemIds;
	}

	private static function _relationDao(){
		static $dao;
		if(is_null($dao)){
			SOY2::import("module.plugins.supplier_manager.domain.SOYShop_SupplierRelationDAO");
			$dao = SOY2DAOFactory::create("SOYShop_SupplierRelationDAO");
		}
		return $dao;
	}
}
