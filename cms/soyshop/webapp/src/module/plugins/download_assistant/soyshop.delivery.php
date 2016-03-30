<?php

class DownloadAssistantDeliveryModule extends SOYShopDelivery{

	function onSelect(CartLogic $cart){

		$module = new SOYShop_ItemModule();
		$module->setId("download_assistant");
		$module->setName("ダウンロード");
		$module->setType("delivery_module");	//typeを指定しておくといいことがある
		$module->setPrice(0);
		$module->setIsVisible(false);
		$cart->addModule($module);

		//属性の登録
		$cart->setOrderAttribute("delivery_download", "配送方法", $this->getName());
	}

	function getName(){
		return "ダウンロード販売";
	}

	function getDescription(){

		$html = array();
		$html[] = "ダウンロード販売";
		return implode("", $html);
	}
}
SOYShopPlugin::extension("soyshop.delivery", "download_assistant", "DownloadAssistantDeliveryModule");
?>