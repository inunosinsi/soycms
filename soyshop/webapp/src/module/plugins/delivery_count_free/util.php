<?php
class DeliveryCountFreeConfigUtil{
	
	public static function getPrice(){

		//default 購入数量が税込み10個未満の時は送料1000円
		return SOYShop_DataSets::get("delivery.count_free.price", array(
			"item_count"           => 10,
			"shipping_fee"         => 1000,
			"default_shipping_fee" => 0
		));

	}
	
	/**
	 * 北海道 (1) と沖縄 (47) の配送料
	 * 離島も同じ料金だが自動判別できないので除外する
	 */
	public static function getSpecialPrice(){

		return SOYShop_DataSets::get("delivery.count_free.special_price", array(
			"0"    => 0,
			"10" => 3000,
			"50" => 5000,
			"100" => 10000,
			"300" => 15000
		));
	}
	
	/**
	 * 料金計算
	 */
	public static function getShippingFee($totalCount, $address = array()){

		if(isset($address["area"]) && $address["area"] == 1 OR $address["area"] == 47){
			//北海道・沖縄の料金
			$config = self::getSpecialPrice();

			foreach($config as $shoppingCount => $fee){
				$count = (int)$shoppingCount;
	
				if($count <= $totalCount){
					$returnValue = $fee;
				}else{
					break;
				}
			}
			
			return (int)$returnValue;
			
		}else{
			$config = self::getPrice();
			
			//item_price未満ならshipping_fee, それ以上ならdefault_shipping_fee
			if($totalCount < (int)$config["item_count"]){
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

		return SOYShop_DataSets::get("delivery.count_free.discount", array(
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
		$title = SOYShop_DataSets::get("delivery.count_free.title","配送料");
		return $title;
	}

	public static function getDescription(){

		try{
			$text = SOYShop_DataSets::get("delivery.count_free.description");
		}catch(Exception $e){
			//default
			$text = "■離島へのお届け、梱包サイズ130以上又は30kg以上の商品につきましては、自動計算の送料とは別に追加の送料を頂戴します。後ほど当店より案内メールでお知らせいたします。";
		}

		return $text;
	}
	public static function getDliveryTimeConfig(){

		try{
			$times = SOYShop_DataSets::get("delivery.count_free.delivery_time_config");
		}catch(Exception $e){
			$times = array("希望なし","午前中","12時～14時","14時～16時","16時～18時","18時～21時");//default
		}

		return $times;
	}
}