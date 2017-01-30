<?php
class SOYShop_ItemModule {
	
	const TYPE_TAX = "tax";
	
    private $id;

    private $name;

    private $type;

    private $price;

    private $config;

	/**
	 * 商品代金に含まれているかどうか
	 * （消費税なら内税か外税か）
	 */
	private $isInclude = false;

	/**
	 * 顧客に見せるかどうか（メール、マイページ）
	 */
	private $isVisible = true;

    function getId() {
    	return $this->id;
    }
    function setId($id) {
    	$this->id = $id;
    }
    function getName() {
    	return $this->name;
    }
    function setName($name) {
    	$this->name = $name;
    }
    function getPrice() {
    	return $this->price;
    }
    function setPrice($price) {
    	$this->price = $price;
    }
    function getConfig() {
    	return $this->config;
    }
    function setConfig($config) {
    	$this->config = $config;
    }

    function getIsInclude() {
    	return $this->isInclude;
    }
    function setIsInclude($isInclude) {
    	$this->isInclude = $isInclude;
    }

    function isInclude(){
    	return (boolean)$this->isInclude;
    }

    function getType() {
    	return $this->type;
    }
    function setType($type) {
    	$this->type = $type;
    }

    function getIsVisible() {
    	return $this->isVisible;
    }
    function setIsVisible($isVisible) {
    	$this->isVisible = $isVisible;
    }

    function isVisible(){
    	return $this->getIsVisible();
    }
}
?>