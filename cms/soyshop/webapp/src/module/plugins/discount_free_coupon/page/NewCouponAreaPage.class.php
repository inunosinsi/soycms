<?php

class NewCouponAreaPage extends WebPage{

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.arrival_new_order.fn.fn", ".php");
	}

	function execute(){
		parent::__construct();

		$histories = self::_get();

		$cfgs = soyshop_get_arrival_config("discount_free_coupon");

		$cnt = count($histories);
		DisplayPlugin::toggle("more_coupon_history", ($cnt > $cfgs["count"]));
		DisplayPlugin::toggle("has_coupon_history", ($cnt > 0));
		DisplayPlugin::toggle("no_coupon_history", ($cnt === 0));

		if($cnt > $cfgs["count"]) $histories = array_slice($histories, 0, $cfgs["count"]);

		list($couponIds, $userIds, $orderIds) = self::_getCouponIdsAndUserIdsAndOrderIds($histories);
		$this->createAdd("coupon_history_list", "_common.Coupon.CouponHistoryComponent", array(
			"list" => $histories,
			"userNameList" => ($cnt > 0) ? SOY2Logic::createInstance("logic.user.UserLogic")->getUserNameListByUserIds($userIds) : array(),
			"trackingNumberList" => ($cnt > 0) ? SOY2Logic::createInstance("logic.order.OrderLogic")->getTrackingNumberListByIds($orderIds) : array(),
			"couponNameList" => ($cnt > 0) ? SOY2Logic::createInstance("module.plugins.discount_free_coupon.logic.DiscountFreeCouponLogic")->getCouponNameListByIds($couponIds) : array()
		));

		$this->addLabel("display_count", array(
			"text" => $cfgs["count"]	
		));
	}

	private function _get(){
		SOY2::import("module.plugins.discount_free_coupon.domain.SOYShop_CouponHistoryDAO");
		$dao = SOY2DAOFactory::create("SOYShop_CouponHistoryDAO");

		$cfgs = soyshop_get_arrival_config("discount_free_coupon");

		try{
			$res = $dao->executeQuery(
				"SELECT user_id, order_id, coupon_id, create_date, price FROM soyshop_coupon_history ".
				"WHERE create_date > :date ".
				"ORDER BY create_date DESC ".
				"LIMIT ".$cfgs["count"],
				array(":date" => strtotime("-".$cfgs["period"]."month"))
			);
		}catch(Exception $e){
			return array();
		}
		if(!count($res)) return array();

		$_arr = array();
		foreach($res as $v){
			$_arr[] = $dao->getObject($v);
		}
		return $_arr;
	}

	private function _getCouponIdsAndUserIdsAndOrderIds(array $histories){
		if(!count($histories)) return array(array(), array(), array());

		$couponIds = array();
		$userIds = array();
		$orderIds = array();
		foreach($histories as $history){
			if(!is_numeric(array_search($history->getCouponId(), $couponIds))) {
				$couponIds[] = (int)$history->getCouponId();
			}

			if(!is_numeric(array_search($history->getUserId(), $userIds))) {
				$userIds[] = (int)$history->getUserId();
			}

			if(!is_numeric(array_search($history->getOrderId(), $orderIds))) {
				$orderIds[] = (int)$history->getOrderId();
			}
		}

		return array($couponIds, $userIds, $orderIds);
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
