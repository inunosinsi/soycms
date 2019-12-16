<?php

class CommonPointPayment_OrderCustomfieldModule extends SOYShopOrderCustomfield{

	/**
	 * 所持しているポイント以上に使えてしまう事態を防ぐための処理
	 * CartLogic->orderのトランザクション内で呼ばれる
	 * 通常はsoyshop.point.paymentのCommonPointPayment->orderでエラーになるのでここまで来ない
	 * {@inheritDoc}
	 * @see SOYShopOrderCustomfield::order()
	 */
	function order(CartLogic $cart){

		SOY2Logic::createInstance("module.plugins.common_point_base.logic.PointBaseLogic", array("cart" => $cart))->checkIfPointIsEnoughAndValidBeforeOrder();

		if(DEBUG_MODE)$cart->log("point: ". $cart->getAttribute("point_payment"));
		if(DEBUG_MODE)$cart->log("point: ". var_export($cart->getModule("point_payment"),true));
		if(DEBUG_MODE)$cart->log("point: ". var_export($cart->getOrderAttribute("point_payment"),true));

	}

}
SOYShopPlugin::extension("soyshop.order.customfield", "common_point_payment", "CommonPointPayment_OrderCustomfieldModule");
