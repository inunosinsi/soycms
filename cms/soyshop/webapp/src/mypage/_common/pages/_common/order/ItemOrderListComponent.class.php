<?php

class ItemOrderListComponent extends HTMLList{

	private $itemDao;

	protected function populateItem($itemOrder) {

		$item = $this->getItem($itemOrder->getItemId());
		
		$this->addLink("item_link", array(
			"link" => soyshop_get_item_detail_link($item)
		));

		$this->addLink("item_code", array(
			"text" => (strlen($item->getCode()) > 0) ? $item->getCode() : "deleted item " . $itemOrder->getItemId(),
		));
		
		$this->addLabel("item_code_plain", array(
			"text" => (strlen($item->getCode()) > 0) ? $item->getCode() : "deleted item " . $itemOrder->getItemId()
		));
		
		$this->addImage("item_small_image", array(
			"src" => $item->getAttribute("image_small")
		));

		$this->addLabel("item_name", array(
			"text" => $itemOrder->getItemName()
		));
		
		$delegate = SOYShopPlugin::invoke("soyshop.item.option", array(
			"mode" => "display",
			"item" => $itemOrder,
		));

		$this->addLabel("item_option", array(
			"html" => $delegate->getHtmls()
		));

		$this->addLabel("item_price", array(
			"text" => number_format($itemOrder->getItemPrice())
		));

		$this->addLabel("item_count", array(
			"text" => number_format($itemOrder->getItemCount())
		));

		$this->addLabel("item_total_price", array(
			"text" => number_format($itemOrder->getTotalPrice())
		));
	}

	/**
	 * @return object#SOYShop_Item
	 * @param itemId
	 */
	function getItem($itemId){
		try{
			return $this->itemDao->getById($itemId);
		}catch(Exception $e){
			return new SOYShop_Item();
		}
	}
	
	function setItemDao($itemDao){
		$this->itemDao = $itemDao;
	}
}
?>