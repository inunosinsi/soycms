<?php

class B2OrderCsvUtil {

	public static function getConfig(){
		return SOYShop_DataSets::get("b2_order_csv", array(
				"number" => "",
				"name" => "",
				"auto_insert_shipping_date" => 0,	//出荷予定日
				"neko_pos" => 0,	//ネコポス
				"customer_code" => ""	//ご請求先顧客コード
		));
	}

	public static function saveConfig($values){
		SOYShop_DataSets::put("b2_order_csv", $values);
	}

	public static function getCompanyInfomation(){
		static $info;
		if(is_null($info)) {
			SOY2::import("domain.config.SOYShop_ShopConfig");
			$info = SOYShop_ShopConfig::load()->getCompanyInformation();
		}
		return $info;
	}

	public static function getSelectedDeliveryMethod($orderId){
		$attrs = soyshop_get_order_object($orderId)->getAttributeList();
		if(!count($attrs)) return "";

		foreach($attrs as $key => $v){
			if(preg_match('/delivery.*/',$key)){
				return $key;
			}
		}

		return "";
	}

	public static function isDaibiki($orderId){
		$attrs = soyshop_get_order_object($orderId)->getAttributeList();
		if(!count($attrs)) return false;

		foreach($attrs as $key => $v){
			if(preg_match('/payment_daibiki/',$key)){
				return true;
			}
		}

		return false;
	}

	public static function mbConvertKana($str){
		$str = mb_convert_kana($str, "a");
		return str_replace(array("ー","－","ｰ"),"-",$str);
	}

	public static function removeHyphen($str){
		$str = mb_convert_kana($str, "a");
		return str_replace(array("ー","－","ｰ"),"",$str);
	}

	public static function convertSpace($str){
		return str_replace("　"," ",$str);
	}

	public static function getOrderItemsByOrderId($orderId){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("order.SOYShop_ItemOrderDAO");

		try{
			return $dao->getByOrderId($orderId);
		}catch(Exception $e){
			return array();
		}
	}
}
