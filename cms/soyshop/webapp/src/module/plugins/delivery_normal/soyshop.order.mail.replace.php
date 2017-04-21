<?php

class DeliveryNormalMailReplace extends SOYShopOrderMailReplace{

	function strings(){
		return array(
			"DELIVERY_METHOD" => "配送方法",
			"DELIVERY_DATE" => "お届け日",
			"DELIVERY_TIME" => "お届け時間",
			"POSTAGE" => "送料"
		);
	}

	function replace(SOYShop_Order $order, $content){
		$list = $order->getAttributeList();
		
		//配送方法
		$method = (isset($list["delivery_normal"])) ? $list["delivery_normal"]["value"] : "";
		$content = str_replace("#DELIVERY_METHOD#", $method, $content);
		
		//お届け日
		$attr = (isset($list["delivery_normal.date"])) ? $list["delivery_normal.date"] : array();
		$content = str_replace("#DELIVERY_DATE#", self::getDeliveryDate($attr), $content);
		
		//お届け時間
		$time = (isset($list["delivery_normal.time"])) ? $list["delivery_normal.time"]["value"] : "";
		$content = str_replace("#DELIVERY_TIME#", $time, $content);
		
		//送料
		$list = $order->getModuleList();
		$postage = (isset($list["delivery_normal"])) ? number_format($list["delivery_normal"]->getPrice()) : "";
		$content = str_replace("#POSTAGE#", $postage, $content);
		
		return $content;
	}
		
	private function getDeliveryDate($attr){
		if(!isset($attr["value"])) return "";
		
		if($attr["value"] == "指定なし"){
			
			//発送メールとその他のメール以外は指定なしで送る
			if(!isset($_GET["type"]) || $_GET["type"] == "order" || $_GET["type"] == "confirm" || $_GET["type"] == "payment") return $attr["value"];
			
			SOY2::import("module.plugins.delivery_normal.util.DeliveryNormalUtil");
			$conf = DeliveryNormalUtil::getDeliveryDateConfig();
			if(isset($conf["delivery_date_mail_insert_date"]) && (int)$conf["delivery_date_mail_insert_date"] > 0){
				return date("Y-m-d", time() + $conf["delivery_date_mail_insert_date"] * 24 * 60 * 60);
			}
		}
		
		return $attr["value"];
	}
}

SOYShopPlugin::extension("soyshop.order.mail.replace", "delivery_normal", "DeliveryNormalMailReplace");
