<?php

class NewCouponAreaPage extends WebPage{

	private $configObj;

	function __construct(){}

	function execute(){
		parent::__construct();

		$histories = self::_get();

		$cnt = count($histories);
		DisplayPlugin::toggle("more_coupon_history", ($cnt > 5));
		DisplayPlugin::toggle("has_coupon_history", ($cnt > 0));
		DisplayPlugin::toggle("no_coupon_history", ($cnt === 0));

		if($cnt > 5) $histories = array_slice($histories, 0, 5);

		list($couponIds, $userIds, $orderIds) = self::_getCouponIdsAndUserIdsAndOrderIds($histories);
		$this->createAdd("coupon_history_list", "_common.Coupon.CouponHistoryComponent", array(
			"list" => $histories,
			"userNameList" => ($cnt > 0) ? SOY2Logic::createInstance("logic.user.UserLogic")->getUserNameListByUserIds($userIds) : array(),
			"trackingNumberList" => ($cnt > 0) ? SOY2Logic::createInstance("logic.order.OrderLogic")->getTrackingNumberListByIds($orderIds) : array(),
			"couponNameList" => ($cnt > 0) ? SOY2Logic::createInstance("module.plugins.discount_free_coupon.logic.DiscountFreeCouponLogic")->getCouponNameListByIds($couponIds) : array()
		));
	}

	private function _get(){
		SOY2::import("module.plugins.discount_free_coupon.domain.SOYShop_CouponHistoryDAO");
		$dao = SOY2DAOFactory::create("SOYShop_CouponHistoryDAO");
		$dao->setLimit(6);

		try{
			return $dao->get();
		}catch(Exception $e){
			return array();
		}
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
