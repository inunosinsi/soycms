<?php
class CommonItemOptionMail extends SOYShopOrderMail{

	/**
	 * メール本文を取得
	 * @return string
	 */
	function getMailBody(SOYShop_Order $order){
		SOY2::import("module.plugins.common_item_option.util.ItemOptionUtil");
		$opts = ItemOptionUtil::getOptions();
		if(!count($opts)) return "";


		$itemOrders = soyshop_get_item_orders($order->getId());
		if(!count($itemOrders)) return "";

		$res = array();

		$res[] = "";
		$res[] = MessageManager::get("ITEM_OPTION");
		$res[] = "-----------------------------------------";
		foreach($itemOrders as $item){
			$flag = false;
			$attributes = $item->getAttributeList();
			foreach($attributes as $obj){
				if(isset($obj)) $flag = true;
			}

			if(!$flag) continue;

			$res[] = $item->getOpenItemName() . ":";
			foreach($attributes as $key => $value){
				if(strlen($value) > 0){
					$res[] = self::getOptionName($opts[$key]) . ":" . $value;
				}
			}
			$res[] = "";
		}

		$res[] = "";

		return implode("\n", $res);
	}

	//メールの文面に表示される順番の指定。数字を小さくすれば上位表示
	function getDisplayOrder(){
		return 200;//delivery系は200番台
	}

	private function getOptionName($values){
		if(defined("SOYSHOP_PUBLISH_LANGUAGE") && SOYSHOP_PUBLISH_LANGUAGE != "jp"){
			return (isset($values["name_" . SOYSHOP_PUBLISH_LANGUAGE]) && strlen($values["name_" . SOYSHOP_PUBLISH_LANGUAGE])) ? $values["name_" . SOYSHOP_PUBLISH_LANGUAGE] : $values["name"];
		}else{
			return $values["name"];
		}
	}
}

SOYShopPlugin::extension("soyshop.order.mail.user", "common_item_option", "CommonItemOptionMail");
SOYShopPlugin::extension("soyshop.order.mail.confirm", "common_item_option", "CommonItemOptionMail");
SOYShopPlugin::extension("soyshop.order.mail.admin", "common_item_option", "CommonItemOptionMail");
