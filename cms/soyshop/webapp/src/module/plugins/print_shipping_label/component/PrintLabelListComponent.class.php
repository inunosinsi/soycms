<?php

class PrintLabelListComponent extends HTMLList{
	
	private $company;
	
	protected function populateItem($order){
		
		//前のページでPrintShippingLabelUtilを読み込んでいる
		
		//お届け先情報
		$addrs = $order->getAddressArray();
		
		list($zip1, $zip2) = self::convertZipCode($addrs["zipCode"]);
		
		$this->addLabel("zip_code1", array(
			"text" => $zip1
		));

		$this->addLabel("zip_code2", array(
			"text" => $zip2
		));
		
		$this->addLabel("tel", array(
			"text" => self::convertTel($addrs["telephoneNumber"])
		)); 
		
		$this->addLabel("address", array(
			"html" => self::convertAddress($addrs)
		));
		$this->addLabel("customer", array(
			"text" => $addrs["name"]
		));
		
		list($zip1, $zip2) = self::convertZipCode($this->company["address1"]);
		
		$this->addLabel("shop_zip_code1", array(
			"text" => $zip1
		));

		$this->addLabel("shop_zip_code2", array(
			"text" => $zip2
		));
		
		$this->addLabel("shop_tel", array(
			"text" => self::convertTel($this->company["telephone"])
		));
		
		$this->addLabel("shop_address", array(
			"html" => $this->company["address2"]
		));
		
		$this->addLabel("shopname", array(
			"text" => $this->shopname
		));
		
		$dates = self::getDateArray();
		
		$this->addLabel("date_m", array(
			"text" => (strlen($dates[1])) ? (int)$dates[1] : ""
		));

		$this->addLabel("date_d", array(
			"text" => (strlen($dates[2])) ? (int)$dates[2] : ""
		));
		
		$this->addLabel("product", array(
			"html" => (isset($_POST["ShippingProduct"])) ? nl2br($_POST["ShippingProduct"]) : ""
		));
		
		$selectedTime = self::getSelectedTime($order->getAttributeList());
		
		//配送時間でどこに○をするか？
		foreach(array("am", "12", "14", "16", "18", "20") as $i => $val){
			
			$this->addModel("delivery_time_" . $val, array(
				"visible" => (($i + 1) === $selectedTime)
			));
		}
		
		//代金引換額
		$this->addLabel("order_price", array(
			"text" => self::getOrderPrice($order->getPrice())
		));
	}
	
	private function convertZipCode($zipcode){
		$zipcode = str_replace(array("-", "ー", "ー"), "", $zipcode);
		$zipcode = mb_convert_kana($zipcode, "a");
		
		if(strlen($zipcode) !== 7) return array("", "");
		
		$zip1 = substr($zipcode, 0, 3);
		$zip2 = substr($zipcode, 3);
		
		return array($zip1, $zip2);
	}
	
	private function convertTel($tel){
		$tel = str_replace(array("-", "ー", "ー"), "", $tel);
		return mb_convert_kana($tel, "a");
	}
	
	private function convertAddress($addrs){
		$pref = SOYShop_Area::getAreaText($addrs["area"]);
		return $pref . htmlspecialchars($addrs["address1"], ENT_QUOTES, "UTF-8") . htmlspecialchars($addrs["address2"], ENT_QUOTES, "UTF-8");
	}
	
	/**
	 * 配送は何時にするか？
	 * nullはなし
	 * 1:午前中
	 * 2:12時〜
	 * 3:14時〜
	 * 4:16時〜
	 * 5:18時〜
	 * 6:20時〜
	 */
	private function getSelectedTime($attrs){
		$time = self::getDeliveryTime($attrs);
		if(strpos($time, "午前中") === 0) return 1;
		
		$time = substr($time, 0, 2);
		$time = mb_convert_kana($time, "a");
		
		if(!strlen($time) || $time == "?") return null;
		
		if(strpos($time, "12") === 0) return 2;
		if(strpos($time, "14") === 0) return 3;
		if(strpos($time, "16") === 0) return 4;
		if(strpos($time, "18") === 0) return 5;
		if(strpos($time, "20") === 0) return 6;
		
		return null;
	}
	
	private function getDeliveryTime($attrs){
		$time = null;
		
		foreach($attrs as $key => $attr){
			if(strpos($key, "delivery") !== false && strpos($key, ".time") !== false){
				$time = $attr["value"];
				break;
			}
		}
		
		return $time;
	}
	
	private function getDateArray(){
		if(!isset($_POST["ShippingDate"]) || !strlen($_POST["ShippingDate"])) return array("", "", "");
		return explode("-", $_POST["ShippingDate"]);
	}
	
	private function getOrderPrice($price){
		if(
			SHIPPING_LABEL_COMPANY == PrintShippingLabelUtil::COMPANY_KURONEKO && 
			SHIPPING_LABEL_TYPE == PrintShippingLabelUtil::TYPE_CONNECT
		){
			return $price;
		}
		
		return "";
	}
	
	function setCompany($company){
		$this->company = $company;
	}
	
	function setShopname($shopname){
		$this->shopname = $shopname;
	}
}