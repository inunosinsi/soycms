<?php

class CommonOrderDateCustomfieldMailModule extends SOYShopOrderMail{

	/**
	 * メール本文を取得
	 * @return string
	 */
	function getMailBody(SOYShop_Order $order){

		$dao = SOY2DAOFactory::create("order.SOYShop_OrderDateAttributeDAO");
		$list = SOYShop_OrderDateAttributeConfig::load();
		$array = array();
		//リストの再配列
		foreach($list as $obj){
			$array[$obj->getFieldId()]["label"] = $obj->getLabel();
			$array[$obj->getFieldId()]["type"] = $obj->getType();
		}
		if(count($array) === 0) return;
		$list = $array;

		try{
			$attributes = $dao->getByOrderId($order->getId());
		}catch(Exception $e){
			$attributes = array();
		}

		if(!count($attributes)) return;

		$array = array();
		foreach($attributes as $obj){
			if(isset($list[$obj->getFieldId()]["type"]) && is_string($obj->getValue1()) && strlen($obj->getValue1()) > 0){
				$res = array();
				$res[] = $list[$obj->getFieldId()]["label"];

				switch($list[$obj->getFieldId()]["type"]){
					case SOYShop_OrderDateAttribute::CUSTOMFIELD_TYPE_DATE:
						$res[] = soyshop_convert_date_string((int)$obj->getValue1());
						break;
					case SOYShop_OrderDateAttribute::CUSTOMFIELD_TYPE_PERIOD:
						$res[] = soyshop_convert_date_string((int)$obj->getValue1()) . " ～ " . soyshop_convert_date_string((int)$obj->getValue2());
						break;
				}
				$res[] = "";

				$array[] = implode("\n", $res);
			}
		}
		return "\n" . implode("\n", $array);
	}

	function getDisplayOrder(){
		return 200;//delivery系は200番台
	}
}

SOYShopPlugin::extension("soyshop.order.mail.user", "common_order_date_customfield", "CommonOrderDateCustomfieldMailModule");
SOYShopPlugin::extension("soyshop.order.mail.confirm", "common_order_date_customfield", "CommonOrderDateCustomfieldMailModule");
SOYShopPlugin::extension("soyshop.order.mail.admin", "common_order_date_customfield", "CommonOrderDateCustomfieldMailModule");
