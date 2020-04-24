<?php
/*
 * Created on 2009/07/28
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

class DeliverySameDayShippingDelivery extends SOYShopDelivery{

	private $config;

	function onSelect(CartLogic $cart){
		
		self::prepare();
				
		$module = new SOYShop_ItemModule();
		$module->setId("delivery_same_day_shipping");
		$module->setName($this->getName());
		$module->setType("delivery_module");	//typeを指定しておくといいことがある
		$module->setPrice($this->getPrice());
		$cart->addModule($module);
				
		$cart->setOrderAttribute("delivery_same_day_shipping", MessageManager::get("METHOD_DELIVERY"), $this->getName());
				
		$cart->setOrderAttribute("delivery_same_day_shipping.shipping_date", "配送予定日", $_POST["DeliveryPlugin"]["shippingDate"]);
		$cart->setOrderAttribute("delivery_same_day_shipping.arrival_date", "到着予定日", $_POST["DeliveryPlugin"]["arrivalDate"]);		
	}

	function getName(){
		self::prepare();
		return (isset($this->config["title"])) ? $this->config["title"] : "";
	}

	function getDescription(){
		self::prepare();
		include_once(dirname(__FILE__) . "/cart/DeliverySameDayShippingCartPage.class.php");
		$form = SOY2HTMLFactory::createInstance("DeliverySameDayShippingCartPage");
		$form->setConfigObj($this);
		$form->setCart($this->getCart());
		$form->setPluginConfig($this->config);
		$form->execute();
		return $form->getObject();
	}
	
	function getPrice(){
		$prices = DeliverySameDayShippingUtil::getPrice();

		$cart = $this->getCart();

		$free = DeliverySameDayShippingUtil::getFreePrice();

		if(isset($free["free"]) && $cart->getItemPrice() >= $free["free"]){
			$price = 0;
		}else{
			$customer = $cart->getCustomerInformation();
			$address = $cart->getAddress();
			$area = $address["area"];
			$price = (isset($prices[$area])) ? (int)$prices[$area] : 0;
		}
		
		if($price > 0) return $price;
	}
	
	private function prepare(){
		if(!$this->config){
			SOY2::import("module.plugins.delivery_same_day_shipping.util.DeliverySameDayShippingUtil");
			$this->config = DeliverySameDayShippingUtil::getConfig();
		}
	}
}
SOYShopPlugin::extension("soyshop.delivery", "delivery_same_day_shipping", "DeliverySameDayShippingDelivery");
?>