<?php

class SOYShop_ComplexPage extends SOYShop_PageBase{

	private $complexPageBlocks;

	/**
	 * 空のブロックを追加
	 */
	function addBlock($blockId){

		if(strlen($blockId)<1 || !preg_match("/[a-zA-Z0-9_]+/",$blockId)){
			$blockId = "block_" . count($this->blocks);
		}

		$blocks = $this->getBlocks();

		$block = new SOYShop_ComplexPageBlock();
		$block->setBlockId($blockId);

		$blocks[$blockId] = $block;
		$this->setBlocks($blocks);

		return $blockId;

	}

	/**
	 * ブロックを設定
	 */
	function setBlock($blockId,SOYShop_ComplexPageBlock $block){
		$blocks = $this->getBlocks();

		$block->setBlockId($blockId);

		$blocks[$blockId] = $block;
		$this->setBlocks($blocks);
	}

	function removeBlock($blockId){
		$blocks = $this->getBlocks();
		if(isset($blocks[$blockId]))unset($blocks[$blockId]);
		$this->setBlocks($blocks);
	}

	function getBlocks(){
		$blocks = (is_string($this->complexPageBlocks)) ? soy2_unserialize($this->complexPageBlocks) : array();
		if(!is_array($blocks))$blocks = array();
		return $blocks;
	}
	function setBlocks($blocks){
		$this->setComplexPageBlocks(soy2_serialize($blocks));
	}
	function getComplexPageBlocks(){
		return $this->complexPageBlocks;
	}

	function setComplexPageBlocks($blocks) {
		$this->complexPageBlocks = $blocks;
	}

	function getTitleFormatDescription(){
		return parent::getCommonFormat();
	}

	function getKeywordFormatDescription(){
		return parent::getCommonFormat();
    }

    function getDescriptionFormatDescription(){
    	return parent::getCommonFormat();
    }
}


class SOYShop_ComplexPageBlock{

	private $blockId;
	private $countStart;
	private $countEnd;
	private $categories = array();

	private $isAndCustomFieldCordination = true;
	private $customFields = array(
		//key => array("value","is_like");
	);

	/* sort */
	private $defaultSort = "name";
	private $customSort = "";
	private $isReverse = false;
	private $params;

	public static function getOperations(){
		return array(
			"="=>"完全に一致",
			"LIKE"=>"部分的に一致",
			"<>" => "一致しない",
			"NOT LIKE" => "部分的に一致しない(含まない)",
		);
	}

	function getBlockId() {
		return $this->blockId;
	}
	function setBlockId($blockId) {
		$this->blockId = $blockId;
	}
	function getCategories() {
		return $this->categories;
	}
	function setCategories($categories) {
		$categories = array_unique($categories);
		$categories = array_diff($categories, array(-1));
		sort($categories);
		$this->categories = $categories;
	}
	function getCustomFields() {
		return $this->customFields;
	}
	function setCustomFields($customFields) {
		$this->customFields = $customFields;
	}
	function getCountStart() {
		return $this->countStart;
	}
	function setCountStart($countStart) {
		if(strlen($countStart) > 0)$countStart = (int)$countStart;
		$this->countStart = $countStart;
	}
	function getCountEnd() {
		return $this->countEnd;
	}
	function setCountEnd($countEnd) {
		if(strlen($countEnd) > 0)$countEnd = (int)$countEnd;
		$this->countEnd = $countEnd;
	}

	function isAndCustomFieldCordination() {
		return (boolean)$this->isAndCustomFieldCordination;
	}

	function getIsAndCustomFieldCordination() {
		return $this->isAndCustomFieldCordination;
	}
	function setIsAndCustomFieldCordination($isAndCustomFieldCordination) {
		$this->isAndCustomFieldCordination = $isAndCustomFieldCordination;
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

	function getParams(){
		return $this->params;
	}
	function setParams($params){
		$this->params = $params;
	}
}
