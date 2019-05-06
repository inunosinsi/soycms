<?php

class CategoryCordinationListComponent extends HTMLList{

	private $blockId;

	function populateItem($entity,$key){

		$this->addSelect("category_list", array(
			"name" => "Block[" . $this->getBlockId() . "][categories][]",
			"options" => self::getCategories(),
			"selected" => $entity,
			"property" => "name"
		));
	}

	private static function getCategories(){
		static $categories;
		if(is_null($categories)){
			$categories = SOY2DAOFactory::create("shop.SOYShop_CategoryDAO")->get();
		}

		return $categories;
	}


	function getBlockId() {
		return $this->blockId;
	}
	function setBlockId($blockId) {
		$this->blockId = $blockId;
	}
}
