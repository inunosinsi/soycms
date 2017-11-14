<?php
/**
 * @class ErrorPage
 * @date 2009-07-16T16:47:00+09:00
 * @author SOY2HTMLFactory
 */
class ErrorPage extends MainCartPageBase{

	function __construct(){
		parent::__construct();

		$cart = CartLogic::getCart();
		$cart->setAttribute("page","Cart01");

	}
}
