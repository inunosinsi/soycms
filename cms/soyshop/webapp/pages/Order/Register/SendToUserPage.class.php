<?php
include(dirname(__FILE__) . "/common.php");

class SendToUserPage extends WebPage{

    function __construct($args) {

		$cart = AdminCartLogic::getCart();
		$cart->setAttribute("address_key", -1);
		$cart->save();

		SOY2PageController::jump("Order.Register");
   }

}
