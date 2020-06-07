<?php
class DiscountItemStockOption extends SOYShopItemOptionBase{

	private $discountLogic;

	/**
	 * 商品情報の下に表示される情報
	 * @param htmlObj, integer index
	 * @return string html
	 */
	function onOutput($htmlObj, $index){

		$cart = CartLogic::getCart();

		$items = $cart->getItems();
		if(!isset($items[$index])){
			return "";
		}

		return $this->getHtml($items[$index]->getItemId());
	}


	/**
	 * 注文確定後の注文詳細の商品情報の下に表示される
	 * @param object SOYShop_ItemOrder
	 * @return string html
	 */
	function display($item){
		//管理画面側で割引率を表示したい場合は、割引率をデータベースに保持しておく必要がある
//		return $this->getHtml($item->getItemId());
	}

	function getHtml($itemId){
		$html = "";

		if(isset($itemId)){
			$this->prepare();
			$rate = $this->discountLogic->getDiscountRate(soyshop_get_item_object($itemId));
			if($rate > 0){
				$html = "<span style=\"color:#FF0000;\">" . $rate . "%引き" . "</span>";
			}
		}

		return $html;
	}

	function prepare(){
		if(!$this->discountLogic) $this->discountLogic = SOY2Logic::CreateInstance("module.plugins.discount_item_stock.logic.DiscountLogic");
	}
}

SOYShopPlugin::extension("soyshop.item.option", "discount_item_stock", "DiscountItemStockOption");
