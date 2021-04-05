<?php

class SearchItemListComponent extends HTMLList{

	private $detailLink;
	private $categories;
	private $orderDAO;

	protected function populateItem($item, $key) {

		$this->addLabel("item_id", array(
			"text" => $item->getId()
		));

		$this->addInput("item_check", array(
			"name" => "items[]",
			"value" => $item->getId(),
			"onchange" => '$(\'#items_operation\').show();'
		));

		$this->addLabel("item_publish", array(
			"text" => $item->getPublishText()
		));

		$imagePath = soyshop_convert_file_path_on_admin($item->getAttribute("image_small"));
		if(!strlen($imagePath)) $imagePath = soyshop_get_item_sample_image();
		$this->addImage("item_small_image", array(
            //"src" => "/" . SOYSHOP_ID . "/im.php?src=" . $imagePath . "&width=60",	//im.phpが使えなくなった
			"src" => $imagePath,
			"attr:style" => "width:60px;"

        ));

		$this->addLabel("sale_text", array(
			"text" => " ".MessageManager::get("ITEM_ON_SALE"),
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

		$this->addLabel("item_stock", array(
			"text" => soy2_number_format($item->getStock())
		));

		$this->addLabel("item_category", array(
			"text" => (is_numeric($item->getCategory()) && isset($this->categories[$item->getCategory()])) ? $this->categories[$item->getCategory()]->getName() : "-"
		));

		$detailLink = $this->getDetailLink() . $item->getId();
		$this->addLink("detail_link", array(
			"link" => $detailLink
		));

		$this->addLabel("order_count", array(
			"text" => soy2_number_format(self::_getOrderCount($item->getId()))
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

	function getOrderDAO() {
		return $this->orderDAO;
	}
	function setOrderDAO($orderDAO) {
		$this->orderDAO = $orderDAO;
	}

	private function _getOrderCount($id){
		if(!is_numeric($id)) return 0;
		try{
			return $this->orderDAO->countByItemId($id);
		}catch(Exception $e){
			return 0;
		}
	}
}
