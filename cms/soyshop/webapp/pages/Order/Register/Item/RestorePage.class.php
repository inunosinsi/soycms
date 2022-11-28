<?php

class RestorePage extends WebPage {

	function __construct(){
		if(soy2_check_token()){
			if(SOY2Logic::createInstance("logic.order.admin.AdminOrderLogic")->restore()){
				SOY2PageController::jump("Order.Register.Item?successed");
			}
		}

		SOY2PageController::jump("Order.Register.Item?failed");
	}
}
