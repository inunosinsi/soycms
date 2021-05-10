<?php

class ItemListComponent extends HTMLList{

	private $detailLink;
	private $categories=array();

	private $stockLogic;

	function populateItem($item, $key){

		$this->addLabel("ranking", array(
			"text" => $key + 1
		));

		$imagePath = soyshop_convert_file_path_on_admin($item->getAttribute("image_small"));
		if(!strlen($imagePath)) $imagePath = soyshop_get_item_sample_image();
		$this->addImage("item_small_image", array(
			//"src" => "/" . SOYSHOP_ID . "/im.php?src=" . $imagePath . "&width=60",	//im.phpが使えなくなった
			"src" => $imagePath,
			"attr:style" => "width:60px;"
        ));

		$this->addLabel("item_id", array(
			"text" => $item->getId()
		));

		$this->addLabel("update_date", array(
			"text" => print_update_date($item->getUpdateDate())
		));

		$this->addLabel("item_publish", array(
			"text" => $item->getPublishText()// . ($item->isOnSale() ? MessageManager::get("ITEM_ON_SALE") : "")
		));
		$this->addLabel("sale_text", array(
			"text" => " " . MessageManager::get("ITEM_ON_SALE"),
			"visible" => $item->isOnSale()
		));

		$this->addLabel("item_name", array(
			"text" => $item->getName()
		));

		$this->addLabel("item_code", array(
			"text" => $item->getCode()
		));

		$this->addLabel("item_price", array(
			"text" => soy2_number_format($item->getPrice())
		));
		$this->addModel("is_sale", array(
			"visible" => $item->isOnSale()
		));
		$this->addLabel("sale_price", array(
			"text" => soy2_number_format($item->getSalePrice())
		));

		$this->addInput("item_stock_input", array(
			"name" => "Stock[" . $item->getId() . "]",
			"value" => $item->getStock(),
			"style" => "width:80px;text-align:right;"
		));

		$detailLink = $this->getDetailLink() . $item->getId();
		$this->addLink("detail_link", array(
			"link" => $detailLink
		));

		$this->addLabel("ships_waiting_count", array(
			"text" => $this->stockLogic->getCountShipsWaiting($item->getId())
		));

		$this->addLabel("order_count", array(
//			"text" => number_format(self::getOrderCount($item))
		));

		$categoryId = (is_numeric($item->getCategory())) ? (int)$item->getCategory() : 0;
		$this->addLabel("item_category", array(
			"text" => (is_array($this->categories) && isset($this->categories[$categoryId])) ? $this->categories[$categoryId]->getNameWithStatus() : "-"
		));
	}

	function getDetailLink() {
		return $this->detailLink;
	}
	function setDetailLink($detailLink) {
		$this->detailLink = $detailLink;
	}

	function getCategories() {
		return $this->categories;
	}
	function setCategories($categories) {
		$this->categories = $categories;
	}

	function setStockLogic($stockLogic){
		$this->stockLogic = $stockLogic;
	}
}
