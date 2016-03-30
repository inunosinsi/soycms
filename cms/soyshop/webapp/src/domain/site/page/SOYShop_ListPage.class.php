<?php
class SOYShop_ListPage extends SOYShop_PageBase{

	const TYPE_CATEGORY = "category";
	const TYPE_FIELD = "field";
	const TYPE_CUSTOM = "custom";

	private $type = self::TYPE_CATEGORY;	//category,field,custom

	/* category */
	private $categories = array();
	private $defaultCategory;

	/* field */
	private $fieldId;
	private $fieldValue;
	private $useParameter = false;

	/* custom */
	private $moduleId;
	private $limit = 10;

	/* sort */
	private $defaultSort = "name";
	private $customSort = "";
	private $isReverse = false;


	private $currentCategory;


	function getType() {
		if(strlen($this->type) < 1) return self::TYPE_CATEGORY;
		return $this->type;
	}
	function setType($type) {
		$this->type = $type;
	}
	function getCategories() {
		return $this->categories;
	}
	function setCategories($categories) {
		if(is_string($categories)) $categories = explode(",",$categories);
		if(is_object($categories)) $categories = (array)$categories;
		foreach($categories as $key => $value){
			if(empty($value)){
				unset($categories[$key]);
			}
		}
		$categories = array_unique($categories);
		$this->categories = $categories;
	}
	function getFieldId() {
		return $this->fieldId;
	}
	function setFieldId($fieldId) {
		$this->fieldId = $fieldId;
	}
	function getFieldValue() {
		return $this->fieldValue;
	}
	function setFieldValue($fieldValue) {
		$this->fieldValue = $fieldValue;
	}
	function getUseParameter() {
		return $this->useParameter;
	}
	function setUseParameter($useParameter) {
		$this->useParameter = $useParameter;
	}
	function isUseParameter(){
		return (boolean)$this->getUseParameter();
	}
	function getModuleId() {
		return $this->moduleId;
	}
	function setModuleId($moduleId) {
		$this->moduleId = $moduleId;
	}

	function getDefaultCategory() {
		return $this->defaultCategory;
	}
	function setDefaultCategory($defaultCategory) {
		$this->defaultCategory = $defaultCategory;
	}

	function getLimit() {
		return $this->limit;
	}
	function setLimit($limit) {
		$this->limit = max(1,(int)$limit);
	}

	function getDefaultSort() {
		return $this->defaultSort;
	}
	function setDefaultSort($defaultSort) {
		$this->defaultSort = $defaultSort;
	}
	function getCustomSort() {
		return $this->customSort;
	}
	function setCustomSort($customSort) {
		$this->customSort = $customSort;
	}
	function getIsReverse() {
		return $this->isReverse;
	}
	function setIsReverse($isReverse) {
		$this->isReverse = $isReverse;
	}
	function getCurrentCategory() {
		return $this->currentCategory;
	}
	function setCurrentCategory($currentCategory) {
		$this->currentCategory = $currentCategory;
	}

	function getTitleFormatDescription(){
    	$html = array();

    	$html[] = "カテゴリー名:%CATEGORY_NAME%";

    	return implode("<br />", $html);
    }

    function getKeywordFormatDescription(){
    	$html = array();
    	$html[] = "ショップ名:%SHOP_NAME%";
    	return implode("<br />", $html);
    }

    function getDescriptionFormatDescription(){
    	$html = array();
    	$html[] = "ショップ名:%SHOP_NAME%";
    	$html[] = "カテゴリー名:%CATEGORY_NAME%";
    	return implode("<br />", $html);
    }

    function convertPageTitle($title){
    	if($this->currentCategory){
    		return str_replace("%CATEGORY_NAME%", $this->currentCategory->getName(), $title);
    	}
    	return $title;
    }
}
?>