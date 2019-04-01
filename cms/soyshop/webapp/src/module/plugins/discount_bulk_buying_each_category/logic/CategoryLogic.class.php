<?php

class CategoryLogic extends SOY2LogicBase {

	function __construct(){

	}

	function getCategoryList(){
		$categories = SOY2DAOFactory::create("shop.SOYShop_CategoryDAO")->get();
		if(!count($categories)) return array();

		$list = array();
		foreach($categories as $category){
			$list[$category->getId()] = $category->getName();
		}
		return $list;
	}
}
