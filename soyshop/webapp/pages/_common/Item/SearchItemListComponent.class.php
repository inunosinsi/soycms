<?php

class SearchItemListComponent extends HTMLList{

	private $detailLink;
	private $categories;

	protected function populateItem($entity, $key) {

		$this->addLabel("item_id", array(
			"text" => $entity->getId()
		));

		$this->addInput("item_check", array(
			"name" => "items[]",
			"value" => $entity->getId(),
			"onchange" => '$(\'#items_operation\').show();'
		));

		$this->addLabel("item_publish", array(
			"text" => $entity->getPublishText()
		));

		$smallImagePath = $entity->getAttribute("image_small");
		$imagePath = (is_string($smallImagePath)) ? soyshop_convert_file_path_on_admin($smallImagePath) : "";
		if(!strlen($imagePath)) $imagePath = soyshop_get_item_sample_image();
		$this->addImage("item_small_image", array(
            //"src" => "/" . SOYSHOP_ID . "/im.php?src=" . $imagePath . "&width=60",	//im.phpが使えなくなった
			"src" => $imagePath,
			"attr:style" => "width:60px;"

        ));

		$this->addLabel("sale_text", array(
			"text" => " ".MessageManager::get("ITEM_ON_SALE"),
			"visible" => $entity->isOnSale()
		));

		$this->addLabel("item_name", array(
			"text" => $entity->getName()
		));

		$this->addLabel("item_code", array(
			"text" => $entity->getCode()
		));

		$this->addLabel("item_price", array(
			"text" => soy2_number_format($entity->getPrice())
		));
		$this->addModel("is_sale", array(
			"visible" => $entity->isOnSale()
		));
		$this->addLabel("sale_price", array(
			"text" => soy2_number_format($entity->getSalePrice())
		));

		$this->addLabel("item_stock", array(
			"text" => soy2_number_format($entity->getStock())
		));

		$this->addLabel("item_category", array(
			"text" => (is_numeric($entity->getCategory()) && isset($this->categories[$entity->getCategory()])) ? $this->categories[$entity->getCategory()]->getName() : "-"
		));

		$detailLink = $this->getDetailLink() . $entity->getId();
		$this->addLink("detail_link", array(
			"link" => $detailLink
		));

		$this->addLabel("order_count", array(
			"text" => (is_numeric($entity->getId())) ? soy2_number_format(self::_getOrderCount($entity->getId())) : 0
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

	private function _getOrderCount(int $id){
		try{
			return self::_dao()->countByItemId($id);
		}catch(Exception $e){
			return 0;
		}
	}

	private function _dao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("order.SOYShop_ItemOrderDAO");
		return $dao;
	}
}
