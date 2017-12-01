<?php

class ItemOrderListComponent extends HTMLList{

	private $itemDao;

	protected function populateItem($itemOrder) {

		$item = self::getItem($itemOrder->getItemId());

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
			"src" => soyshop_convert_file_path($item->getAttribute("image_small"), $item)
		));

		$this->addLabel("item_name", array(
			"text" => (strlen($item->getCode())) ? $itemOrder->getItemName() : "---"
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

		//子商品
		if(is_numeric($item->getType())){
			$parent = self::getParentItem($item->getType());
		}else{
			$parent = new SOYShop_Item();
		}

		/** 親商品関連のタグ **/
		$this->addLink("parent_link", array(
			"text" => $parent->getOpenItemName(),
			"link" => soyshop_get_item_detail_link($parent)
		));
		$this->addLabel("parent_name_plain", array(
			"text" => $parent->getOpenItemName(),
		));

		$this->addLabel("parent_code", array(
			"text" => $parent->getCode(),
		));

		$this->addImage("parent_small_image", array(
			"src" => soyshop_convert_file_path($parent->getAttribute("image_small"), $parent)
		));

		$this->addImage("parent_large_image", array(
			"src" => soyshop_convert_file_path($parent->getAttribute("image_large"), $parent)
		));
	}

	private function getParentItem($itemId){
		try{
			return $this->itemDao->getById($itemId);
		}catch(Exception $e){
			return new SOYShop_Item();
		}
	}

	/**
	 * @return object#SOYShop_Item
	 * @param itemId
	 */
	private function getItem($itemId){
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
