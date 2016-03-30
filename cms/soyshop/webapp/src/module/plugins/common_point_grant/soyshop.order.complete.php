<?php
class CommonPointGrantOrderComplete extends SOYShopOrderComplete{

	function execute(SOYShop_Order $order){
		$cart = CartLogic::getCart();
		$logic = SOY2Logic::createInstance("module.plugins.common_point_base.logic.PointBaseLogic");
		$totalPoint = $logic->getTotalPointAfterPaymentPoint($cart, $order);
		
		if($totalPoint > 0){
			$logic->insertPoint($order, (int)$totalPoint);
		}
	}
}

SOYShopPlugin::extension("soyshop.order.complete", "common_point_grant", "CommonPointGrantOrderComplete");
?>