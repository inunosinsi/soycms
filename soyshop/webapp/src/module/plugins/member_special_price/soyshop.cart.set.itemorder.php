<?php
class MemberSpecialPriceCartSetItemOrder extends SOYShopCartSetItemOrderBase{

	function setItemOrder(SOYShop_Item $item, $count){

		$price = self::logic()->getSpecialPrice($item);

		//登録する
		if(!is_null($price) && is_numeric($price)){
			$obj = new SOYShop_ItemOrder();
			$obj->setItemId($item->getId());
			$obj->setItemCount($count);
			$obj->setItemPrice($price);
			$obj->setTotalPrice($price * $count);
			$obj->setItemName($item->getName());

			return $obj;
		}


		//nullで返せば通常の商品挿入の処理を行う
		return null;
	}

	function logic(){
		static $logic;
		if(is_null($logic)) $logic = SOY2Logic::createInstance("module.plugins.member_special_price.logic.SpecialPriceLogic");
		return $logic;
	}
}
SOYShopPlugin::extension("soyshop.cart.set.itemorder", "member_special_price", "MemberSpecialPriceCartSetItemOrder");
