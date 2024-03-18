<?php

/**
 * @table MultiLanguageLabelRelation
 */
class MultiLanguageLabelRelation {

	/**
	 * @column parent_label_id
	 */
	private $parentId;

	/**
	 * @column child_label_id
	 */
	private $childId;

	private $lang;

	function getParentId(){
		return $this->parentId;
	}
	function setParentId($parentId){
		$this->parentId = $parentId;
	}
	
	function getChildId(){
		return $this->childId;
	}
	function setChildId($childId){
		$this->childId = $childId;
	}

	function getLang(){
		return $this->lang;
	}
	function setLang($lang){
		$this->lang = $lang;
	}
}