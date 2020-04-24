<?php
SOY2::imports("module.plugins.discount_free_coupon.domain.*");
class DiscountFreeCouponOrderComplete extends SOYShopOrderComplete{

	function execute(SOYShop_Order $order){

		$couponDao = SOY2DAOFactory::create("SOYShop_CouponDAO");

		$values = $order->getAttribute("discount_free_coupon.code");
		if(!isset($values["value"])) return;
		$couponCode = $values["value"];

		try{
			$coupon = $couponDao->getByCouponCodeAndNoDelete($couponCode);
		}catch(Exception $e){
			return;
		}

		//値引き額の取得
		$modules = $order->getModuleList();
		if(isset($modules["discount_free_coupon"])){
			$discountPrice = abs($modules["discount_free_coupon"]->getPrice());
		}else{
			$discountPrice = 0;
		}


		$couponHistoryDao = SOY2DAOFactory::create("SOYShop_CouponHistoryDAO");

		$obj = new SOYShop_CouponHistory();
		$obj->setUserId($order->getUserId());
		$obj->setOrderId($order->getId());
		$obj->setCouponId($coupon->getId());
		$obj->setPrice($discountPrice);

		try{
			$couponHistoryDao->insert($obj);
		}catch(Exception $e){
			//
		}
	}
}

SOYShopPlugin::extension("soyshop.order.complete", "discount_free_coupon", "DiscountFreeCouponOrderComplete");
