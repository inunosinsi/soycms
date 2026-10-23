<?php

class OrderHistoryOnMyPage extends WebPage{

	private $configObj;

	function __construct(){
		parent::__construct();

		SOY2::import("module.plugins.arrival_new_order.fn.fn", ".php");

		$histories = SOY2DAOFactory::create("order.SOYShop_OrderStateHistoryDAO")->getByIds(self::_getHistoryIds());
		$isHistory = count($histories);

		DisplayPlugin::toggle("no_history", !$isHistory);
		DisplayPlugin::toggle("is_history", $isHistory);

		SOY2::import("module.plugins.order_edit_on_mypage.component.HistoryOnMyPageListComponent");
		$this->createAdd("history_list", "HistoryOnMyPageListComponent", array(
			"list" => $histories,
			"userIds" => ($isHistory) ? self::_getUserIds($histories) : array()
		));
	}

	private function _getHistoryIds(){
		$cacheLogic = SOY2Logic::createInstance("module.plugins.order_edit_on_mypage.logic.HistoryIdCacheLogic");
		$ids = $cacheLogic->readCache();
		if(count($ids)) return $ids;

		$cfgs = soyshop_get_arrival_config("order_edit_on_mypage");

		$dao = new SOY2DAO();
		try{
			$results = $dao->executeQuery(
				"SELECT id FROM soyshop_order_state_history ".
				"WHERE author LIKE '顧客%' ".
				"AND (content LIKE '注文合計%' OR content LIKE '注文番号%') ".
				"AND order_date > :date ".
				"ORDER BY order_date DESC LIMIT ".$cfgs["count"],
				array(":date" => strtotime("-".$cfgs["period"]."month"))
			);
		}catch(Exception $e){
			var_dump($e);
			$results = array();
		}
		unset($dao);
		if(!count($results)) return array();

		$ids = array();
		foreach($results as $res){
			if(!isset($res["id"]) || !is_numeric($res["id"])) continue;
			$ids[]  = (int)$res["id"];
		}
		$cacheLogic->saveCache($ids);
		return $ids;
	}

	private function _getUserIds(array $histories){
		$orderIds = array();
		foreach($histories as $hist){
			$orderIds[] = (int)$hist->getOrderId();
		}
		if(!count($orderIds)) return array();

		$dao = new SOY2DAO();
		try{
			$results = $dao->executeQuery(
				"SELECT id, user_id FROM soyshop_order ".
				"WHERE id IN (" . implode(",", $orderIds) . ")"
			);
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
