<?php
SOY2DAOFactory::importEntity("SOYShop_DataSets");
include_once(dirname(__FILE__) . "/common.php");

class YuchoPaymentModule extends SOYShopPayment{

	function onSelect(CartLogic $cart){
		//属性の登録
		$cart->setOrderAttribute("payment_yucho","支払方法","ゆうちょ銀行口座へのお支払い");

		//後でチェックするため見えないモジュールを追加
		$module = new SOYShop_ItemModule();
		$module->setId("payment_yucho");
		$module->setName("支払い方法：ゆうちょ銀行");
		$module->setType("payment_module");	//typeを指定しておくといいことがある
		$module->setPrice(0);
		$module->setIsVisible(false);
		$cart->addModule($module);
	}

	function getName(){
		return "ゆうちょ銀行";
	}

	function getDescription(){
		return nl2br($this->getAccount());
	}

	/**
	 * 振込先の取得
	 */
	function getAccount(){
		$array = PaymentYuchoCommon::getConfigText();
		$res = (isset($array["text"])) ? $array["text"] : "";

		//replace
		if(isset($array["account"])){
			$res = str_replace("#ACCOUNT#", $array["account"], $res);
		}
		
		return $res;
	}
}
SOYShopPlugin::extension("soyshop.payment","payment_yucho","YuchoPaymentModule");
?>