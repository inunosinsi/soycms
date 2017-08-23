<?php

class NewCouponAreaPage extends WebPage{
	
	private $configObj;
	
	function __construct(){}
	
	function execute(){
		parent::__construct();
		
		SOY2::imports("module.plugins.discount_free_coupon.domain.*");
		SOY2::imports("module.plugins.discount_free_coupon.logic.*");
		
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
				
		$this->createAdd("coupon_history_list", "_common.Coupon.CouponHistoryComponent", array(
			"list" => $histories,
			"userDao" => SOY2DAOFactory::create("user.SOYShop_UserDAO"),
			"orderDao" => SOY2DAOFactory::create("order.SOYShop_OrderDAO"),
			"couponDao" => $couponDao
		));
	}
	
	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
?>