<?php

class RelativeItemListComponent extends HTMLList{

	protected function populateItem($entity, $key){

		$this->addInput("item_code_input", array(
			"name" => "relative_items[]",
			"value" => (isset($entity) && strlen($entity)) ? $entity : "",
			"id" => "relative_items_" . $key
		));

		$this->addModel("label_for", array(
			"attr:for" => "relative_items_" . $key
		));

		$this->addLabel("item_name", array(
			"text" => (isset($entity) && strlen($entity)) ? self::_getItemName($entity) : ""
		));
	}

	private function _getItemName(string $code){
		try{
			return self::_dao()->getByCode($code)->getName();
		}catch(Exception $e){
			return "該当の商品が見付かりません";
		}
	}

	private function _dao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		return $dao;
	}
}
