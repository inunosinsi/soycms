<?php

class NoticeMailLogic extends SOY2LogicBase {

	function convertMailContent(string $str, SOYShop_Item $item){
		//商品情報
		$str = str_replace("#ITEM_CODE#", $item->getCode(), $str);
		$str = str_replace("#ITEM_NAME#", $item->getName(), $str);

		if(soy2_strpos($str, "#SHOP_NAME#") < 0) return $str;

		return SOY2Logic::createInstance("logic.mail.MailLogic")->convertMailContent($str, soyshop_get_user_object(0), soyshop_get_order_object(0));
	}
}
