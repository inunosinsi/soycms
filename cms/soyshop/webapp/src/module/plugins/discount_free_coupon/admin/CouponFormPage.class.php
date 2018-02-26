<?php

class CouponFormPage extends WebPage {

	private $categoryLogic;
	private $cart;

	function __construct(){
		$this->categoryLogic = SOY2Logic::createInstance("module.plugins.discount_free_coupon.logic.CouponCategoryLogic");
	}

	function execute(){
		parent::__construct();

		//顧客IDが取得できるか？
		$userId = $this->cart->getCustomerInformation()->getId();
		DisplayPlugin::toggle("no_coupon_code_area", is_null($userId));
		DisplayPlugin::toggle("coupon_code_area", isset($userId));

		$error = $this->cart->getAttribute("discount_free_coupon.error");
		DisplayPlugin::toggle("coupon_error", strlen($error));
		$this->addLabel("coupon_error", array(
			"text" => $error
		));

		$categoryList = (isset($userId)) ? $this->categoryLogic->getCategoryList() : array();	//処理の削減
		DisplayPlugin::toggle("has_category_list", count($categoryList));

		$this->addSelect("category", array(
			"name" => "customfield_module[discount_free_coupon][categoryId]",
			"options" => $categoryList,
			"selected" => $this->cart->getAttribute("discount_free_coupon.category")
		));

		$this->addInput("code", array(
			"name" => "customfield_module[discount_free_coupon][couponCode]",
			"value" => $this->cart->getAttribute("discount_free_coupon.code")
		));

		$this->addLabel("category_code_js", array(
			"html" => $this->categoryLogic->createCodePrefixList()
		));
		$this->addLabel("code_js", array(
			"html" => file_get_contents(dirname(dirname(__FILE__)) . "/js/code.js")
		));
	}

	function setCart($cart){
		$this->cart = $cart;
	}
}
