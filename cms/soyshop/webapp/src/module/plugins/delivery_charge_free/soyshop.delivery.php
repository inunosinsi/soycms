<?php
include_once(dirname(__FILE__) . "/util.php");
/*
 * 無料配送モジュール
 */
class DeliveryChargeFreeModule extends SOYShopDelivery{
	
	function onSelect(CartLogic $cart){

		//割引を先に行う
		$module = new SOYShop_ItemModule();
		$module->setId("delivery_charge_free");
		$module->setName("送料");
		$module->setType("delivery_module");	//typeを指定しておくといいことがある
		$module->setPrice($this->getPrice());
		$cart->addModule($module);

		//属性の登録
		$cart->setOrderAttribute("delivery_charge_free", "配送方法", $this->getName());

		if(isset($_POST["delivery_charge_free_date"]) && strlen($_POST["delivery_charge_free_date"]) > 0){
			$cart->setOrderAttribute("delivery_charge_free.date", "配達希望日", $_POST["delivery_charge_free_date"]);
		}else{
			$cart->setOrderAttribute("delivery_charge_free.date", "配達希望日", "指定なし");
		}

		if(isset($_POST["delivery_charge_free_time"]) && strlen($_POST["delivery_charge_free_time"]) > 0){
			$cart->setOrderAttribute("delivery_charge_free.time", "配達時間", $_POST["delivery_charge_free_time"]);
		}else{
			$cart->setOrderAttribute("delivery_charge_free.time", "配達時間", "指定なし");
		}
	}

	function getName(){
		return DeliveryChargeFreeConfigUtil::getTitle();
	}

	function getDescription(){
		include_once(dirname(__FILE__) . "/cart.php");
		$form = SOY2HTMLFactory::createInstance("DeliveryChargeFreeCartFormPage");
		$form->setCart($this->getCart());
		$form->execute();
		return $form->getObject();
	}
	
	function getPrice(){
		return DeliveryChargeFreeConfigUtil::getShippingFee($this->getCart()->getItemPrice(), $this->getCart()->getAddress());
	}
}
SOYShopPlugin::extension("soyshop.delivery", "delivery_charge_free", "DeliveryChargeFreeModule");
?>