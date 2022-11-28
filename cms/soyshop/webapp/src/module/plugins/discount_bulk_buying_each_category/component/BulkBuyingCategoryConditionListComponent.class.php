<?php

class BulkBuyingCategoryConditionListComponent extends HTMLList {

	private $categories;

	function populateItem($entity, $key){
		$this->addSelect("condition_category", array(
			"name" => "category_condition[" . $key . "][category]",
			"options" => $this->categories,
			"selected" => (isset($entity["category"])) ? $entity["category"] : null
		));

		$this->addInput("condition_lowest_price", array(
			"name" => "category_condition[" . $key . "][price]",
			"value" => (isset($entity["price"])) ? $entity["price"] : 0
		));

		$this->addInput("condition_lowest_total", array(
			"name" => "category_condition[" . $key . "][total]",
			"value" => (isset($entity["total"])) ? $entity["total"] : 0
		));

		$this->addInput("condition_lowest_amount", array(
			"name" => "category_condition[" . $key . "][amount]",
			"value" => (isset($entity["amount"])) ? $entity["amount"] : 0
		));

		$this->addSelect("condition_combination", array(
			"name" => "category_condition[" . $key . "][combination]",
			"options" => DiscountBulkBuyingUtil::getCombinationType(),
			"selected" => (isset($entity["combination"])) ? $entity["combination"] : 0
		));

		$this->addSelect("condition_discount_type", array(
			"name" => "category_condition[" . $key . "][type]",
			"options" => DiscountBulkBuyingUtil::getDiscountType(),
			"selected" => (isset($entity["type"])) ? $entity["type"] : null,
			"attr:id" => "condition_discount_type_" . $key
		));

		$this->addModel("condition_discount_amount_area", array(
			"attr:id" => "condition_discount_amount_" . $key
		));

		$this->addModel("condition_discount_percent_area", array(
			"attr:id" => "condition_discount_percent_" . $key
		));

		$this->addInput("condition_discount_amount", array(
			"name" => "category_condition[" . $key . "][discount][amount]",
			"value" => (isset($entity["discount"]["amount"])) ? $entity["discount"]["amount"] : ""
		));

		$this->addInput("condition_discount_percent", array(
			"name" => "category_condition[" . $key . "][discount][percent]",
			"value" => (isset($entity["discount"]["percent"])) ? $entity["discount"]["percent"] : ""
		));

		$this->addInput("condition_apply_amount", array(
			"name" => "category_condition[" . $key . "][apply]",
			"value" => (isset($entity["apply"])) ? $entity["apply"] : ""
		));
	}

	function setCategories($categories){
		$this->categories = $categories;
	}
}
