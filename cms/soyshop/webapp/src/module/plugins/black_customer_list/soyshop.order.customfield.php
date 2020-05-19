<?php
/*
 * Created on 2009/07/28
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

class BlackCustomerListOrderCustomfield extends SOYShopOrderCustomfield{

	function clear(CartLogic $cart){}
	function doPost($param){}
	function order(CartLogic $cart){}
	function hasError($param){}
	function getForm(CartLogic $cart){}

	function display($orderId){
		$userId = self::getLogic()->getUserIdByOrderId($orderId);
		$attr = self::getLogic()->getAttribute($userId);
		if(!is_null($attr->getValue()) && $attr->getValue() == 1){
			return array(array(
				"name" => "ブラックリスト",
				"value" => "ブラックリストに登録されています。",
				"style" => "font-weight:bold;color:#FF0000;"
			));
		}

		return array();
	}

	function edit($orderId){}
	function config($orderId){}

	private function getLogic(){
		static $logic;
		if(is_null($logic)) $logic = SOY2Logic::createInstance("module.plugins.black_customer_list.logic.BlackListLogic");
		return $logic;
	}
}
SOYShopPlugin::extension("soyshop.order.customfield", "black_customer_list", "BlackCustomerListOrderCustomfield");
