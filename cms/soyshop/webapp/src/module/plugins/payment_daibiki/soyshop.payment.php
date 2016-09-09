<?php
SOY2DAOFactory::importEntity("SOYShop_DataSets");
SOY2::import("module.plugins.payment_daibiki.util.PaymentDaibikiUtil");
class DaibikiPaymentModule extends SOYShopPayment{

	private $daibikiLogic;

	function onSelect(CartLogic $cart){

		self::prepare();

		if(!$this->daibikiLogic->checkCartItems()){
			$cart->addErrorMessage("payment","この支払方法（代金引換）は選択できません");
			return false;
		}

		$module = new SOYShop_ItemModule();
		$module->setId("payment_daibiki");
		$module->setType("payment_module");//typeを指定しておくといいことがある
		$module->setName("代金引換手数料");

		//金額から手数料を取得
		$module->setPrice($this->getPrice());

		$cart->addModule($module);

		//属性の登録
		$cart->setOrderAttribute("payment_daibiki","支払方法","代金引換でのお支払い");
	}

	function getName(){
		self::prepare();

		//代引き不可商品があったらこのモジュール自体を表示しない
		if($this->daibikiLogic->checkCartItems()){
			return "代金引換";
		}else{
			return "";
		}
	}

	function getDescription(){
		$res = PaymentDaibikiUtil::getDescriptionConfig();

		if(!$this->currentPrice) $this->currentPrice = $this->getPrice();
		$res = str_replace("#PRICE#", $this->currentPrice,$res);

		return nl2br($res);
	}

	var $currentPrice;

	/**
	 * 料金の取得
	 */
	function getPrice(){
		self::prepare();
		$this->currentPrice = $this->daibikiLogic->getDaibikiPrice();
		return $this->currentPrice;
	}
	
	private function prepare(){
		if(!$this->daibikiLogic) $this->daibikiLogic = SOY2Logic::createInstance("module.plugins.payment_daibiki.logic.DaibikiLogic", array("cart" => $this->getCart()));
	}

}
SOYShopPlugin::extension("soyshop.payment","payment_daibiki","DaibikiPaymentModule");
?>