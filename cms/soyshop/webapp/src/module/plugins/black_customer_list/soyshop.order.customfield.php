<?php

class BlackCustomerListOrderCustomfield extends SOYShopOrderCustomfield{

	function display(int $orderId){
		$userId = self::_logic()->getUserIdByOrderId($orderId);
		$attr = self::_logic()->getAttribute($userId);
		if(!is_null($attr->getValue()) && $attr->getValue() == 1){
			return array(array(
				"name" => "ブラックリスト",
				"value" => "ブラックリストに登録されています。",
				"style" => "font-weight:bold;color:#FF0000;"
			));
		}

		return array();
	}

	private function _logic(){
		static $logic;
		if(is_null($logic)) $logic = SOY2Logic::createInstance("module.plugins.black_customer_list.logic.BlackListLogic");
		return $logic;
	}
}
SOYShopPlugin::extension("soyshop.order.customfield", "black_customer_list", "BlackCustomerListOrderCustomfield");
