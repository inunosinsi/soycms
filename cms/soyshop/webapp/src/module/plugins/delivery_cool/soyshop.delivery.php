<?php
/*
 * Created on 2009/07/28
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

include_once(dirname(__FILE__) . "/common.php");

class DeliveryCoolModule extends SOYShopDelivery{

	function onSelect(CartLogic $cart){

		$module = new SOYShop_ItemModule();
		$module->setId("delivery_cool");
		$module->setName("送料");
		$module->setType("delivery_module");	//typeを指定しておくといいことがある
		$module->setPrice($this->getPrice());
		$cart->addModule($module);

		//属性の登録
		$cart->setOrderAttribute("delivery_cool", "配送方法", $this->getName());

		if(isset($_POST["delivery_time"]) && strlen($_POST["delivery_time"]) > 0){
			$time = $_POST["delivery_time"];
			if(defined("SOYSHOP_IS_MOBILE") && defined("SOYSHOP_MOBILE_CHARSET") && SOYSHOP_MOBILE_CHARSET == "Shift_JIS"){
				$time = mb_convert_encoding($time, "UTF-8", "SJIS");
			}
			$cart->setOrderAttribute("delivery_cool.time", "配達時間", $time);
		}else{
			$cart->setOrderAttribute("delivery_cool.time", "配達時間", "指定なし");
		}
	}

	function getName(){
		return "クール便";
	}

	function getDescription(){
		
		$coolPrice = $this->getCoolPrice();

		$html = array();

		$html[] = "クール便の追加料金は" . $coolPrice . "円です。";

		$html[] = "<table><tr><th>配達時間の指定</th><td>";
		$html[] = '<select name="delivery_time">';

		$time = DeliveryCoolCommon::getDliveryTimeConfig();
		
		$cart = $this->getCart();
		$selected = $cart->getOrderAttribute("delivery_cool.time");
		
		foreach($time as $str){
			if(isset($selected["value"]) && $selected["value"] == $str){
				$html[] = '<option selected="selected">';
			}else{
				$html[] = '<option>';
			}
			$html[] = htmlspecialchars($str, ENT_QUOTES, "UTF-8") . '</option>';
		}
		$html[] = '</select></td></table>';

		return implode("", $html);
	}

	function getPrice(){
		$prices = DeliveryCoolCommon::getPrice();

		$cart = $this->getCart();
		$customer = $cart->getCustomerInformation();

		$address = $cart->getAddress();

		$area = $address["area"];

		$price = (isset($prices[$area])) ? (int)$prices[$area] : 0;
		
		$coolPrice = (int)$this->getCoolPrice();
		
		$price = $price + $coolPrice;

		return $price;
	}
	
	function getCoolPrice(){
		$coolPrice = DeliveryCoolCommon::getCoolPrice();

		return $coolPrice;		
	}

}
SOYShopPlugin::extension("soyshop.delivery", "delivery_cool", "DeliveryCoolModule");
?>