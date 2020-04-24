<?php

class PricePage extends WebPage{

	function __construct($args){
		parent::__construct();

		$item = soyshop_get_item_object($args[0]);

		$this->addLabel("item_name", array(
			"text" => $item->getName()
		));

		include_once(dirname(dirname(__FILE__)) . "/component/PriceListComponent.class.php");
		$this->createAdd("price_list", "PriceListComponent", array(
			"list" => SOY2Logic::createInstance("logic.shop.item.ItemLogic")->getItemPriceListByItemId($item->getId())
		));
	}
}
