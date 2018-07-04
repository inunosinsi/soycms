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

	function config(){
		$times = DeliveryChargeFreeConfigUtil::getDliveryTimeConfig();

		$attrs = $this->getOrder()->getAttributeList();
		$selected = (isset($attrs["delivery_charge_free.time"]["value"])) ? $attrs["delivery_charge_free.time"]["value"] : "";

		$html = array();
		$html[] = "<select name=\"Attribute[delivery_charge_free.time]\">";
		$html[] = "<option></option>";
		if(count($times)){
			foreach($times as $time){
				if($time == $selected){
					$html[] = "<option value=\"" . $time . "\" selected=\"selected\">" . $time . "</option>";
				}else{
					$html[] = "<option value=\"" . $time . "\">" . $time . "</option>";
				}
			}
		}
		$html[] = "</select>";

		return implode("\n", $html);
	}
}
SOYShopPlugin::extension("soyshop.delivery", "delivery_charge_free", "DeliveryChargeFreeModule");
