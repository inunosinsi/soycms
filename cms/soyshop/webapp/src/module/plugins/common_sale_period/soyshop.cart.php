<?php
/*
 */
class SalePeriodCart extends SOYShopCartBase{

	const PLUGIN_ID = "common_sale_period";

	function afterOperation(CartLogic $cart){
		//カートに入っている商品を削除する時は実行しない
		if(isset($_GET["a"]) && $_GET["a"] == "remove") return;

		$items = $cart->getItems();
		$last = end(array_keys($items));
		//今回カートに入れた商品
		$item = self::getItemById($items[$last]->getItemId());
		$price = self::getPriceLogic()->getDisplayPrice($item);

		$items[$last]->setItemPrice($price);
		$items[$last]->setTotalPrice($price * $items[$last]->getItemCount());
	}

	private function getItemById($itemId){
		$itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		try{
			return $itemDao->getById($itemId);
		}catch(Exception $e){
			return new SOYShop_Item();
		}
	}

	private $logic;

	private function getPriceLogic(){
		if(!$this->logic) $this->logic = SOY2Logic::createInstance("module.plugins.". self::PLUGIN_ID . ".logic.PriceLogic");
		return $this->logic;
	}
}
SOYShopPlugin::extension("soyshop.cart", "common_sale_period", "SalePeriodCart");
