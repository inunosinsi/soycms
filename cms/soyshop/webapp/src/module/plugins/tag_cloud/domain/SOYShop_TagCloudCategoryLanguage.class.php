<?php
/**
 * @table soyshop_tag_cloud_category_language
 */
class SOYShop_TagCloudCategoryLanguage {

	/**
	 * @column category_id
	 */
	private $categoryId;
	private $lang;
	private $label;

	function getCategoryId(){
		return $this->categoryId;
	}
	function setCategoryId($categoryId){
		$this->categoryId = $categoryId;
	}

	function getLang(){
		return $this->lang;
	}
	function setLang($lang){
		$this->lang = $lang;
	}

	function getLabel(){
		return $this->label;
	}
	function setLabel($label){
		$this->label = $label;
	}
}