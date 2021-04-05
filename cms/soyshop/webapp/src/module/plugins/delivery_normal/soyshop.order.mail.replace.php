<?php

class DeliveryNormalMailReplace extends SOYShopOrderMailReplace{

	function strings(){
		$strings = array();
		$strings["DELIVERY_METHOD"] = "配送方法";
		$strings["DELIVERY_DATE"] = "お届け日";

		SOY2::import("module.plugins.delivery_normal.util.DeliveryNormalUtil");
		$conf = DeliveryNormalUtil::getDeliveryDateConfig();

		if(isset($conf["delivery_date_mail_insert_date"]) && (int)$conf["delivery_date_mail_insert_date"] > 0){
			$strings["DELIVERY_DATE_AUTO"] = "お届け日(自動)";
		}

		$strings["DELIVERY_TIME"] = "お届け時間";
		$strings["POSTAGE"] = "送料";

		return $strings;
	}

	function replace(SOYShop_Order $order, $content){
		$list = $order->getAttributeList();

		//配送方法
		$method = (isset($list["delivery_normal"])) ? $list["delivery_normal"]["value"] : "";
		$content = str_replace("#DELIVERY_METHOD#", $method, $content);

		//お届け日
		$attr = (isset($list["delivery_normal.date"])) ? $list["delivery_normal.date"] : array("value" => "");
		$content = str_replace("#DELIVERY_DATE#", $attr["value"], $content);

		//お届け日(自動)
		SOY2::import("module.plugins.delivery_normal.util.DeliveryNormalUtil");
		$conf = DeliveryNormalUtil::getDeliveryDateConfig();
		if(isset($conf["delivery_date_mail_insert_date"]) && (int)$conf["delivery_date_mail_insert_date"] > 0){
			$content = str_replace("#DELIVERY_DATE_AUTO#", date("Y-m-d", time() + $conf["delivery_date_mail_insert_date"] * 24 * 60 * 60), $content);
		}

		//お届け時間
		$time = (isset($list["delivery_normal.time"])) ? $list["delivery_normal.time"]["value"] : "";
		$content = str_replace("#DELIVERY_TIME#", $time, $content);

		//送料
		$list = $order->getModuleList();
		$postage = (isset($list["delivery_normal"])) ? soy2_number_format($list["delivery_normal"]->getPrice()) : "";
		$content = str_replace("#POSTAGE#", $postage, $content);

		return $content;
	}
}

SOYShopPlugin::extension("soyshop.order.mail.replace", "delivery_normal", "DeliveryNormalMailReplace");
