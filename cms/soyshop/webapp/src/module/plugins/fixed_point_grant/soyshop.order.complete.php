<?php
class FixedPointGrantOrderComplete extends SOYShopOrderComplete{

	function execute(SOYShop_Order $order){
		//ポイント使用時にポイント値の変更がないため、そのまま取得
		$total = SOY2Logic::createInstance("module.plugins.fixed_point_grant.logic.FixedPointGrantLogic", array("cart" => CartLogic::getCart()))->getTotalPointOnCart($order->getId());
		if($total > 0) SOY2Logic::createInstance("module.plugins.common_point_base.logic.PointBaseLogic")->insertPoint($order, (int)$total);

		/** @ToDo ポイントを使用しつつポイントを取得する仕組み **/
	}
}

SOYShopPlugin::extension("soyshop.order.complete", "fixed_point_grant", "FixedPointGrantOrderComplete");
