<?php
SOY2DAOFactory::importEntity("SOYShop_DataSets");
class DaibikiPaymentModule extends SOYShopPayment{

	function onSelect(CartLogic $cart){

		if(!$this->checkCartItems()){
			$cart->addErrorMessage("payment","この支払方法（代金引換）は選択できません");
			return false;
		}

		$module = new SOYShop_ItemModule();
		$module->setId("payment_daibiki");
		$module->setType("payment_module");//typeを指定しておくといいことがある
		$module->setName("代金引換手数料");

		//金額から手数料を取得
		$module->setPrice($this->getPrice($cart->getTotalPrice()));

		$cart->addModule($module);

		//属性の登録
		$cart->setOrderAttribute("payment_daibiki","支払方法","代金引換でのお支払い");
	}

	function getName(){
		$items = $this->getCart()->getItems();
		$forbidden = SOYShop_DataSets::get("payment_daibiki.forbidden", array());

		//代引き不可商品があったらこのモジュール自体を表示しない
		if($this->checkCartItems()){
			return "代金引換";
		}else{
			return "";
		}
	}

	function getDescription(){
		$res = SOYShop_DataSets::get("payment_daibiki.description","代金引換でのお支払いです。手数料は#PRICE#円です。");

		if(!$this->currentPrice) $this->currentPrice = $this->getPrice();
		$res = str_replace("#PRICE#", $this->currentPrice,$res);

		return nl2br($res);
	}

	var $currentPrice;

	/**
	 * 料金の取得
	 */
	function getPrice(){
		$price = $this->getCart()->getItemPrice();

		$prices = SOYShop_DataSets::get("payment_daibiki.price", array());
		$returnValue = 0;

		foreach($prices as $key => $value){

			if($key <= $price){
				$returnValue = $value;
			}else{
				break;
			}
		}

		$this->currentPrice = $returnValue;

		return $returnValue;
	}

	/**
	 * 代引き不可商品が入っていないかチェック
	 * @return Boolean
	 */
	function checkCartItems(){
		$items = $this->getCart()->getItems();
		$forbidden = SOYShop_DataSets::get("payment_daibiki.forbidden", array());

		//代引き不可商品があったらこのモジュール自体を表示しない
		if(count($forbidden) > 0){
			foreach($items as $itemOrder){
				$itemDAO = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
				$itemId = $itemOrder->getItemId();
				$item = $itemDAO->getById($itemId);
				if(in_array($item->getCode(),$forbidden)){
					return false;
				}

			}
		}
		return true;
	}

}
SOYShopPlugin::extension("soyshop.payment","payment_daibiki","DaibikiPaymentModule");
?>