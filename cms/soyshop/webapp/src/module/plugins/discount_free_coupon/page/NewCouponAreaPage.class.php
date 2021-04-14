<?php

class NewCouponAreaPage extends WebPage{

	private $configObj;

	function __construct(){}

	function execute(){
		parent::__construct();

		SOY2::imports("module.plugins.discount_free_coupon.domain.*");
		//SOY2::imports("module.plugins.discount_free_coupon.logic.*");

		$couponDao = SOY2DAOFactory::create("SOYShop_CouponDAO");
		$couponHistoryDao = SOY2DAOFactory::create("SOYShop_CouponHistoryDAO");
		$couponHistoryDao->setLimit(6);

		try{
			$histories = $couponHistoryDao->get();
		}catch(Exception $e){
			$histories = array();
		}

		DisplayPlugin::toggle("more_coupon_history", (count($histories) > 5));
		DisplayPlugin::toggle("has_coupon_history", (count($histories) > 0));
		DisplayPlugin::toggle("no_coupon_history", (count($histories) === 0));

		$histories = array_slice($histories, 0, 5);

		list($couponIds, $userIds, $orderIds) = self::_getCouponIdsAndUserIdsAndOrderIds($histories);
		$this->createAdd("coupon_history_list", "_common.Coupon.CouponHistoryComponent", array(
			"list" => $histories,
			"userNameList" => SOY2Logic::createInstance("logic.user.UserLogic")->getUserNameListByUserIds($userIds),
			"trackingNumberList" => SOY2Logic::createInstance("logic.order.OrderLogic")->getTrackingNumberListByIds($orderIds),
			"couponNameList" => SOY2Logic::createInstance("module.plugins.discount_free_coupon.logic.DiscountFreeCouponLogic")->getCouponNameListByIds($couponIds)
		));
	}

	private function _getCouponIdsAndUserIdsAndOrderIds($histories){
		if(!is_array($histories) || !count($histories)) return array(array(), array(), array());

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
