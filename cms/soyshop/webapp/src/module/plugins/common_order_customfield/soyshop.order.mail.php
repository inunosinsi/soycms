<?php

class CommonOrderCustomfieldMailModule extends SOYShopOrderMail{

	/**
	 * メール本文を取得
	 * @return string
	 */
	function getMailBody(SOYShop_Order $order){

		$dao = SOY2DAOFactory::create("order.SOYShop_OrderAttributeDAO");
		$list = SOYShop_OrderAttributeConfig::load();
		$array = array();
		//リストの再配列
		foreach($list as $obj){
			$array[$obj->getFieldId()]["label"] = $obj->getLabel();
			$array[$obj->getFieldId()]["type"] = $obj->getType();
		}
		if(count($array) == 0) return;
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
					case "radio":
						if(strlen($obj->getValue1())){
							$msg = $obj->getValue1();
							if(is_string($obj->getValue2()) && strlen($obj->getValue2())){
								$msg .= ":" . $obj->getValue2();
							}
							$res[] = $msg;
						}
						break;
					default:
						if(strlen($obj->getValue1())) $res[] = $obj->getValue1();
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

SOYShopPlugin::extension("soyshop.order.mail.user", "common_order_customfield", "CommonOrderCustomfieldMailModule");
SOYShopPlugin::extension("soyshop.order.mail.confirm", "common_order_customfield", "CommonOrderCustomfieldMailModule");
SOYShopPlugin::extension("soyshop.order.mail.admin", "common_order_customfield", "CommonOrderCustomfieldMailModule");
