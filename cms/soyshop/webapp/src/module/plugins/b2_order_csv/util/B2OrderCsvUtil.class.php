<?php

class B2OrderCsvUtil {

	public static function getConfig(){
		return self::_getConfig();
	}

	private static function _getConfig(){
		static $cnf;
		if(is_null($cnf)){
			$cnf = SOYShop_DataSets::get("b2_order_csv", array(
					"number" => "",
					"name" => "",
					"auto_insert_shipping_date" => 0,	//出荷予定日
					"neko_pos" => 0,	//ネコポス
					"customer_code" => ""	//ご請求先顧客コード
			));
		}
		return $cnf;
	}

	public static function saveConfig($values){
		SOYShop_DataSets::put("b2_order_csv", $values);
	}

	public static function getCompanyInfomation(){
		static $info;
		if(is_null($info)) {
			SOY2::import("domain.config.SOYShop_ShopConfig");
			$cnf = SOYShop_ShopConfig::load();
			$info = $cnf->getCompanyInformation();

			//ショップ名も格納しておく
			$info["shop_name"] = $cnf->getShopName();
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
		return self::_isDaibiki($orderId);
	}

	private static function _isDaibiki($orderId){
		$attrs = soyshop_get_order_object($orderId)->getAttributeList();
		if(!count($attrs)) return false;

		foreach($attrs as $key => $v){
			if(preg_match('/payment_daibiki/',$key)){
				return true;
			}
		}

		return false;
	}

	public static function getInvoiceType($orderId){

		//隠し機能 $_POST["invoice"]がある場合はそれを利用する
		if(isset($_POST["invoice"]) && is_numeric($_POST["invoice"])) return (int)$_POST["invoice"];

		//隠し機能　$_POST["Pattern"][金額]で指定する 例：<input type="hidden" name="Pattern[500]" value="8">
		if(isset($_POST["Pattern"]) && is_array($_POST["Pattern"])){
			$mods = soyshop_get_order_object($orderId)->getModuleList();
			foreach($mods as $moduleId => $mod){
				if(strpos($moduleId, "delivery_") != 0) continue;
				$price = (int)$mod->getPrice();
				if(isset($_POST["Pattern"][$price])) return $_POST["Pattern"][$price];
			}
		}

		//送り状 代引き:2、それ以外:0、ネコポス:7	//代引きを最優先にする
		if(self::_isDaibiki($orderId)) return 2;

		$cnf = self::_getConfig();
		if(isset($cnf["neko_pos"]) && $cnf["neko_pos"] == 1){
			return 7;
		}else{
			return 0;
		}
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

	/** 隠しモードの拡張機能 **/

	//どの列に拡張機能を追加するか？
	public static function getExtInsertNumber(){
		static $n;
		if(is_null($n)){
			$n = 42;	//標準
			$dir = self::_extendDir();
			if(is_dir($dir) && $handle = opendir($dir)) {
				while(($file = readdir($handle)) !== false) {
					preg_match('/(\d*).php/', $file, $tmp);
					if(isset($tmp[1]) && is_numeric($tmp[1]) && (int)$tmp[1] > $n){
						$n = (int)$tmp[1];
					}
				}
			}
		}
		return $n;
	}

	public static function getExtendDir(){
		return self::_extendDir();
	}

	private static function _extendDir(){
		static $dir;
		if(is_null($dir)) $dir = dirname(dirname(__FILE__)) . "/extends/";
		return $dir;
	}
}
