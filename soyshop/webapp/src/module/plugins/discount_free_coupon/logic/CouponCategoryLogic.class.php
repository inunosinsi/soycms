<?php

class CouponCategoryLogic extends SOY2LogicBase {

	function __construct(){
		SOY2::import("module.plugins.discount_free_coupon.domain.SOYShop_CouponCategory");
		SOY2::import("module.plugins.discount_free_coupon.domain.SOYShop_CouponCategoryDAO");
	}

	function getCategoryList(){
		static $list;
		if(is_null($list)){
			$list = array();

			$categories = self::_getCategories();
			if(!count($categories)) return array();

			foreach($categories as $category){
				$list[$category->getId()] = $category->getName();
			}
		}
		return $list;
	}

	function createCodePrefixList(){
		$script = array();
		$script[] = "var prefixList = {" ;

		$categories = self::_getCategories();
		$last = count($categories);
		if($last > 0){
			$counter = 0;
			foreach($categories as $categoryId => $category){
				if($counter++ === $last - 1){
					$script[] = $category->getId() . " : '" . $category->getPrefix() . "'";
				}else{
					$script[] = $category->getId() . " : '" . $category->getPrefix() . "',";
				}
			}
		}

		$script[] = "};";
		return implode("\n", $script);
	}

	function getCategoryNameById($categoryId){
		try{
			return self::dao()->getById($categoryId)->getName();
		}catch(Exception $e){
			return "";
		}
	}

	private function _getCategories(){
		static $categories;
		if(is_null($categories)){
			try{
				$categories = self::dao()->get();
			}catch(Exception $e){
				$categories = array();
			}
		}
		return $categories;
	}

	private function dao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("SOYShop_CouponCategoryDAO");
		return $dao;
	}
}
