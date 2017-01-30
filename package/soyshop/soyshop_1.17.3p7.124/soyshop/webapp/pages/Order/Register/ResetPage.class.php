<?php
include(dirname(__FILE__) . "/common.php");

class ResetPage extends WebPage{

	function __construct() {

		//カートをクリア
		$cart = AdminCartLogic::getCart();
		$cart->clear();

		SOY2PageController::jump("Order.Register");
	}
}