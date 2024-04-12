<?php

/**
 * @table MultiLanguageEntryRelation
 */
class MultiLanguageEntryRelation {

	/**
	 * @column parent_entry_id
	 */
	private $parentId;

	/**
	 * @column child_entry_id
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