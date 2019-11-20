<?php

class CategoryListComponent extends HTMLList{

	private $categoryDao;

	protected function populateItem($entity){

		$this->addInput("category_check", array(
			"name" => "categories[]",
			"value" => $entity->getId(),
			"onchange" => '$(\'#category_operation\').show();'
		));

		$this->addLabel("is_open", array(
			"text" => ($entity->getIsOpen() == SOYShop_Category::IS_OPEN) ? "公開" : "非公開"
		));

		$this->addLabel("name", array(
			"text" => $entity->getName()
		));

		$parent = self::getParent($entity->getParent());
		$this->addLabel("parent", array(
			"text" => (!is_null($parent->getName())) ? $parent->getName() : "---"
		));

		$this->addLabel("alias", array(
			"text" => $entity->getAlias()
		));

		$this->addInput("order_input", array(
			"name" => "Order[" . $entity->getId() . "]",
			"value" => (is_numeric($entity->getOrder()) && $entity->getOrder() > 0) ? $entity->getOrder() : ""
		));

		$this->addLink("detail_link", array(
			"link" => SOY2PageController::createLink("Item.Category.Detail." . $entity->getId())
		));
	}

	private function getParent($parentId){
		if(is_null($parentId)) return new SOYShop_Category();

		try{
			return $this->categoryDao->getById($parentId);
		}catch(Exception $e){
			return new SOYShop_Category();
		}
	}

	function setCategoryDao($categoryDao){
		$this->categoryDao = $categoryDao;
	}
}
