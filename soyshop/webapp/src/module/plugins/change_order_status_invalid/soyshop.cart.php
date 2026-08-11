<?php

class ChangeOrderStatusInvalidCart extends SOYShopCartBase{

	function __construct(){}

	function doOperation(){}

	function afterOperation(CartLogic $cart){}

	function displayPage01(CartLogic $cart){
		self::_changeStatusOlderOrder();
	}

	function displayPage02(CartLogic $cart){
		self::_changeStatusOlderOrder();
	}

	function displayPage03(CartLogic $cart){
		self::_changeStatusOlderOrder();
	}

	function displayPage04(CartLogic $cart){
		self::_changeStatusOlderOrder();
	}

	function displayPage05(CartLogic $cart){
		self::_changeStatusOlderOrder();
	}

	//古い仮登録注文を無効注文(STATUS_INVALID=0)に変更する
	private function _changeStatusOlderOrder(){
		SOY2::import("module.plugins.change_order_status_invalid.util.ChangeOrderStatusInvalidUtil");
		ChangeOrderStatusInvalidUtil::changeInvalidStatusOlderOrder();
	}
}
SOYShopPlugin::extension("soyshop.cart", "change_order_status_invalid", "ChangeOrderStatusInvalidCart");
