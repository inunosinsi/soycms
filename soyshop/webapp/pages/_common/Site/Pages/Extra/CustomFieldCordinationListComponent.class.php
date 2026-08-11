<?php

class CustomFieldCordinationListComponent extends HTMLList{

	private $blockId;

	function populateItem($entity,$key){

		$customFields = self::getCustomFields();

		$this->addSelect("customfield_list", array(
			"name" => "Block[" . $this->getBlockId() . "][customFields][$key][fieldId]",
			"options" => $customFields,
			"selected" => (isset($entity["fieldId"])) ? $entity["fieldId"] : null,
			"property" => "label"
		));

		$this->addInput("field_value", array(
			"name" => "Block[" . $this->getBlockId() . "][customFields][$key][value]",
			"value" => (isset($entity["value"])) ? $entity["value"] : null
		));

		$this->createAdd("field_type","HTMLSelect", array(
			"name" => "Block[" . $this->getBlockId() . "][customFields][$key][type]",
			"selected" => (isset($entity["type"])) ? $entity["type"] : null,
			"options" => SOYShop_ComplexPageBlock::getOperations()
		));
	}

	private static function getCustomFields(){
		static $customFields;
		if(!$customFields){
			$dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
			$customFields = SOYShop_ItemAttributeConfig::load(true);
		}

		return $customFields;
	}


	function getBlockId() {
		return $this->blockId;
	}
	function setBlockId($blockId) {
		$this->blockId = $blockId;
	}
}
