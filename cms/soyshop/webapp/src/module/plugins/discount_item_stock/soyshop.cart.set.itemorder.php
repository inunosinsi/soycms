<?php
class DiscountItemStockCartSetItemOrder extends SOYShopCartSetItemOrderBase{

	function setItemOrder(SOYShop_Item $item, $count){

		$discountLogic = SOY2Logic::createInstance("module.plugins.discount_item_stock.logic.DiscountLogic");
		
		$price = $discountLogic->getDiscountPrice($item);
		
		$obj = new SOYShop_ItemOrder();
		$obj->setItemId($item->getId());
		$obj->setItemCount($count);
		$obj->setItemPrice($price);
		$obj->setTotalPrice($price * $count);
		$obj->setItemName($item->getName());
		
		return $obj;
	}
}
SOYShopPlugin::extension("soyshop.cart.set.itemorder", "discount_item_stock", "DiscountItemStockCartSetItemOrder");
?>