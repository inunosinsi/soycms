<?php
class CommonPointBaseOrderComplete extends SOYShopOrderComplete{

	function execute(SOYShop_Order $order){
		//common_point_grantに移行
	}
}

SOYShopPlugin::extension("soyshop.order.complete", "common_point_base", "CommonPointBaseOrderComplete");
?>