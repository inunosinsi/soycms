<?php

class OrderItemListComponent extends HTMLList{
	
	private $itemDao;
	
	function populateItem($entity){
		$item = $this->getItem($entity);
		
		$this->addLink("item_link", array(
			"link" => soyshop_get_item_detail_link($item)
		));

		$this->addLabel("item_name", array(
			"text" => $entity->getItemName()
		));
		$delegate = SOYShopPlugin::invoke("soyshop.item.option", array(
			"mode" => "display",
			"item" => $entity,
		));
		
		$this->addModel("has_option", array(
			"visible" => (strlen($delegate->getHtmls()) > 0)
		));

		$this->addLabel("item_option", array(
			"html" => $delegate->getHtmls()
		));
	}
	
	function getItem($entity){
		if(!$this->itemDao){
			$this->itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		}
		
		try{
			$item = $this->itemDao->getById($entity->getItemId());
		}catch(Exception $e){
			$item = new SOYShop_Item();
		}
		return $item;
	}
}
?>