<?php
class CommonPointPaymentOrderComplete extends SOYShopOrderComplete{

	function execute(SOYShop_Order $order){
		//ポイント付与系のプラグインで処理を行います
	}
}

SOYShopPlugin::extension("soyshop.order.complete", "common_point_payment", "CommonPointPaymentOrderComplete");
?>