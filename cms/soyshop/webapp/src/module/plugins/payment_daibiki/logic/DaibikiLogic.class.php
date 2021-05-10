<?php

class DaibikiLogic extends SOY2LogicBase{

	private $cart;

	function __construct(){
		SOY2::import("module.plugins.payment_daibiki.util.PaymentDaibikiUtil");
	}

	function getDaibikiPrice(){
		$price = $this->cart->getItemPrice();

		$config = PaymentDaibikiUtil::getConfig();

		//公開側で送料も加味する
		if(
			(!defined("SOYSHOP_ADMIN_PAGE") || !SOYSHOP_ADMIN_PAGE) &&
			(isset($config["include_delivery_price"]) && (int)$config["include_delivery_price"] === 1)
		){
			/** @ToDo 送料分を加算したい **/
		}

		//割引系のプラグインがある場合は割引分を除く
		foreach($this->cart->getModules() as $mod){
			if(!$mod->getIsInclude() && $mod->getPrice() < 0){
				$price += $mod->getPrice();
			}
		}

		return self::calcReturnValue($price);
	}

	function calcReturnValue($total){
		$prices = array();

		//地域ごとの代引き手数料が設定されているか調べる
		if(isset($this->cart)){
			$area = $this->cart->getCustomerInformation()->getArea();
			if(isset($area)){
				$byRegionConfigs = PaymentDaibikiUtil::getPricesByRegionConfig();
				if(isset($byRegionConfigs[$area])){
					$prices = $byRegionConfigs[$area];
				}
			}
		}

		if(!count($prices)) $prices = PaymentDaibikiUtil::getPricesConfig();

		$returnValue = 0;

		foreach($prices as $key => $value){

			if($key <= $total){
				$returnValue = $value;
			}else{
				break;
			}
		}

		return $returnValue;
	}

	function checkCartItems(){
		return self::checkNoFobiddenItem($this->cart->getItems());
	}

	function checkNoFobiddenItem($items){
		$forbidden = PaymentDaibikiUtil::getForbiddenConfig();

		//代引き不可商品があったらこのモジュール自体を表示しない
		if(count($forbidden) > 0){
			foreach($items as $itemOrder){
				$code = soyshop_get_item_object($itemOrder->getItemId())->getCode();
				if(strlen($code) && in_array($code, $forbidden)) return false;
			}
		}
		return true;
	}

	function setCart($cart){
		$this->cart = $cart;
	}
}
