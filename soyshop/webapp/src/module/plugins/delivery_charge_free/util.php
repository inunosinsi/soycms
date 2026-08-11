<?php
class DeliveryChargeFreeConfigUtil{

	public static function getPrice(){

		//default 購入金額が税込み21000円未満の時は送料1000円
		return SOYShop_DataSets::get("delivery.charge_free.price", array(
			"item_price"           => 21000,
			"shipping_fee"         => 1000,
			"default_shipping_fee" => 0
		));
	}
	
	public static function getNotification(){
		return SOYShop_DataSets::get("delivery.charge_free.notification", array(
			"check" => 0,
			"text" => "送料無料まで残り##DIFFERENCE##円です。"
		));
	}

	/**
	 * 北海道 (1) と沖縄 (47) の配送料
	 * 離島も同じ料金だが自動判別できないので除外する
	 */
	public static function getSpecialPrice(){

		return SOYShop_DataSets::get("delivery.charge_free.special_price", array(
			"0"    => 0,
			"1000" => 3000,
			"50000" => 5000,
			"100000" => 10000,
			"300000" => 15000
		));
	}

	/**
	 * 料金計算
	 */
	public static function getShippingFee($item_price, $address = array()){

		if(isset($address["area"]) && $address["area"] == 1 OR $address["area"] == 47){
			//北海道・沖縄の料金
			$config = self::getSpecialPrice();

			foreach($config as $shopping => $fee){

				if($shopping <= $item_price){
					$returnValue = $fee;
				}else{
					break;
				}
			}

			return (int)$returnValue;

		}else{
			$config = self::getPrice();

			//item_price未満ならshipping_fee, それ以上ならdefault_shipping_fee
			if($item_price < $config["item_price"]){
				return $config["shipping_fee"];
			}else{
				return $config["default_shipping_fee"];
			}
		}
	}

	/**
	 * 割引設定
	 */
	public static function getDiscountSetting(){

		return SOYShop_DataSets::get("delivery.charge_free.discount", array(
			"" => ""
		));
	}

	/**
	 * 割引金額取得
	 */
	public static function getDiscountAmount($item_price){

		$config = self::getDiscountSetting();

		foreach($config as $shopping => $percentage){
			if($shopping <= $item_price){
				$discount_rate = $percentage;
			}else{
				break;
			}
		}

		return round($item_price * $discount_rate / 100);
	}

	public static function getTitle(){
		$title = SOYShop_DataSets::get("delivery.charge_free.title","宅配便");
		return $title;
	}

	public static function getDescription(){

		try{
			$text = SOYShop_DataSets::get("delivery.charge_free.description");
		}catch(Exception $e){
			//default
			$text = "■離島へのお届け、梱包サイズ130以上又は30kg以上の商品につきましては、自動計算の送料とは別に追加の送料を頂戴します。後ほど当店より案内メールでお知らせいたします。";
		}

		return $text;
	}
	public static function getDliveryTimeConfig(){

		try{
			$times = SOYShop_DataSets::get("delivery.charge_free.delivery_time_config");
		}catch(Exception $e){
			$times = array("希望なし","午前中","12時～14時","14時～16時","16時～18時","18時～21時");//default
		}

		return $times;
	}
}