<?php

class DiscountBulkBuyingEachCategoryDiscountModule extends SOYShopDiscount{

	function __construct(){
		SOY2::import("module.plugins.discount_bulk_buying_each_category.util.DiscountBulkBuyingUtil");
	}

	function clear(){
		$cart = $this->getCart();
		$cart->removeModule("discount_bulk_buying_each_category");
		$cart->clearOrderAttribute("discount_bulk_buying_each_category");
	}

	/**
	 *
	 */
	function doPost(array $params){
		$cart = $this->getCart();
		$discountLogic = SOY2Logic::createInstance("module.plugins.discount_bulk_buying_each_category.logic.DiscountBulkBuyingLogic", array("items" => $cart->getItems()));

		//割引対象
		if($discountLogic->checkIsApplyDiscount()){

			//各割引額を合算
			$discountTotal = 0;
			$prices = $discountLogic->getDiscountPrices();
			if(count($prices)){
				foreach($prices as $price){
					$discountTotal += $price;
				}
			}

			if($discountTotal > 0){
				$config = DiscountBulkBuyingUtil::getConfig();

				//合計金額から割引
				$module = new SOYShop_ItemModule();
				$module->setId("discount_bulk_buying_each_category");
				$module->setName($config["name"]);
				$module->setType("discount_module");	//typeを指定すると同じtypeのモジュールは同時使用できなくなる
				$module->setPrice(0 - $discountTotal);//負の値
				$cart->addModule($module);

				//注文属性にも入れておく
				$cart->setOrderAttribute("discount_bulk_buying_each_category", $config["name"], number_format((int)$discountTotal). "円割引");
			}
		}
	}

	function getError(){
		return false;
	}

	/**
	 * @return string
	 */
	function getName(){
		$discountLogic = SOY2Logic::createInstance("module.plugins.discount_bulk_buying_each_category.logic.DiscountBulkBuyingLogic", array("items" => $this->getCart()->getItems()));
		if($discountLogic->checkIsApplyDiscount()){
			$config = DiscountBulkBuyingUtil::getConfig();
			return $config["name"];
		}else{
			return "";
		}
	}

	/**
	 * @return string
	 */
	function getDescription(){
		$discountLogic = SOY2Logic::createInstance("module.plugins.discount_bulk_buying_each_category.logic.DiscountBulkBuyingLogic", array("items" => $this->getCart()->getItems()));
		if($discountLogic->checkIsApplyDiscount()){
			$config = DiscountBulkBuyingUtil::getConfig();
			$description = $config["description"];

			/** @ToDo 割引詳細 **/
			$prices = $discountLogic->getDiscountPrices();
			if(!count($prices)) return $prices;

			foreach($prices as $categoryId => $discountPrice){
				$description .= "<br>" . soyshop_get_category_object($categoryId)->getName() . "の割引き：" . number_format($discountPrice) . "円";
			}

			//これがないとdoPostができない
			$description .= "\n<input type=\"hidden\" name=\"discount_module[discount_bulk_buying_each_category]\" value=\"1\">";

			return $description;
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.discount", "discount_bulk_buying_each_category", "DiscountBulkBuyingEachCategoryDiscountModule");
