<?php
/*
 * Created on 2009/07/28
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

class DeliveryNormalModule extends SOYShopDelivery{

	function prepare(){
		SOY2::import("module.plugins.delivery_normal.util.DeliveryNormalUtil");
	}

	function onSelect(CartLogic $cart){
		$this->prepare();

		$module = new SOYShop_ItemModule();
		$module->setId("delivery_normal");
		$module->setName(MessageManager::get("LABEL_POSTAGE"));
		$module->setType("delivery_module");	//typeを指定しておくといいことがある
		$module->setPrice($this->getPrice());
		$cart->addModule($module);

		//属性の登録
		$cart->setOrderAttribute("delivery_normal", MessageManager::get("METHOD_DELIVERY"), $this->getName());

		//配達時間帯の指定を利用するか？
		$useDeliveryTime = DeliveryNormalUtil::getUseDeliveryTimeConfig();
		if($useDeliveryTime["use"] == 1){

			if(isset($_POST["delivery_time"]) && strlen($_POST["delivery_time"]) > 0){
				$time = $_POST["delivery_time"];
				if(defined("SOYSHOP_IS_MOBILE") && defined("SOYSHOP_MOBILE_CHARSET") && SOYSHOP_MOBILE_CHARSET == "Shift_JIS"){
					$time = mb_convert_encoding($time, "UTF-8", "SJIS");
				}
				$cart->setOrderAttribute("delivery_normal.time", MessageManager::get("DELIVERY_TIME"), $time);
			}else{
				$cart->setOrderAttribute("delivery_normal.time", MessageManager::get("DELIVERY_TIME"), MessageManager::get("UNSPECIFIED"));
			}
		}
	}

	function getName(){
		$this->prepare();
		return DeliveryNormalUtil::getTitle();
	}

	function getDescription(){
		include_once(dirname(__FILE__) . "/cart/DeliveryNormalCartPage.class.php");
		$form = SOY2HTMLFactory::createInstance("DeliveryNormalCartPage");
		$form->setConfigObj($this);
		$form->setCart($this->getCart());
		$form->execute();
		return $form->getObject();
	}

	function getPrice(){
		$this->prepare();
		
		$prices = DeliveryNormalUtil::getPrice();

		$cart = $this->getCart();

		$free = DeliveryNormalUtil::getFreePrice();

		if(isset($free["free"]) && $cart->getItemPrice() >= $free["free"]){
			$price = 0;
		}else{
			$customer = $cart->getCustomerInformation();
			$address = $cart->getAddress();
			$area = $address["area"];
			$price = (isset($prices[$area])) ? (int)$prices[$area] : 0;
		}
		return $price;
	}
}
SOYShopPlugin::extension("soyshop.delivery", "delivery_normal", "DeliveryNormalModule");
?>