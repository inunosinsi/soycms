<?php

class CategoryCordinationListComponent extends HTMLList{

	private $blockId;

	function populateItem($entity,$key){

		$this->addSelect("category_list", array(
			"name" => "Block[" . $this->getBlockId() . "][categories][]",
			"options" => soyshop_get_category_objects(),
			"selected" => $entity,
			"property" => "name"
		));
	}

	function getBlockId() {
		return $this->blockId;
	}
	function setBlockId($blockId) {
		$this->blockId = $blockId;
	}
}
