<?php

class OrderHistoryOnMyPage extends WebPage{

	private $configObj;

	function __construct(){
		parent::__construct();

		$histories = SOY2DAOFactory::create("order.SOYShop_OrderStateHistoryDAO")->getByIds(self::_getHistoryIds());

		SOY2::import("module.plugins.order_edit_on_mypage.component.HistoryOnMyPageListComponent");
		$this->createAdd("history_list", "HistoryOnMyPageListComponent", array(
			"list" => $histories,
			"userIds" => (count($histories)) ? self::_getUserIds($histories) : array()
		));
	}

	private function _getHistoryIds(){
		$cacheLogic = SOY2Logic::createInstance("module.plugins.order_edit_on_mypage.logic.HistoryIdCacheLogic");
		$ids = $cacheLogic->readCache();
		if(count($ids)) return $ids;
		try{
			$results = SOY2DAOFactory::create("order.SOYShop_OrderStateHistoryDAO")->executeQuery("SELECT id FROM soyshop_order_state_history WHERE author LIKE '顧客%' AND (content LIKE '注文合計%' OR content LIKE '注文番号%') ORDER BY order_date DESC LIMIT 15");
		}catch(Exception $e){
			$results = array();
		}
		if(!count($results)) return array();

		$ids = array();
		foreach($results as $res){
			if(!isset($res["id"]) || !is_numeric($res["id"])) continue;
			$ids[]  = (int)$res["id"];
		}
		$cacheLogic->saveCache($ids);
		return $ids;
	}

	private function _getUserIds($histories){
		$orderIds = array();
		foreach($histories as $hist){
			$orderIds[] = (int)$hist->getOrderId();
		}
		if(!count($orderIds)) return array();

		$dao = new SOY2DAO();
		try{
			$results = $dao->executeQuery("SELECT id, user_id FROM soyshop_order WHERE id IN (" . implode(",", $orderIds) . ")");
		}catch(Exception $e){
			$results = array();
		}
		unset($dao);
		if(!count($results)) return array();

		$userIds = array();
		foreach($results as $res){
			$userIds[(int)$res["id"]] = (int)$res["user_id"];
		}

		return $userIds;
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
