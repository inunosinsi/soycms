<?php

/**
 * @table GeminiKeywordRelation
 */
class GeminiKeywordRelation {

	/**
	 * @column keyword_id
	 */
	private $keywordId;

	/**
	 * @column entry_id
	 */
	private $entryId;
	private $importance = 1;


	function getKeywordId(){
		return $this->keywordId;
	}
	function setKeywordId($keywordId){
		$this->keywordId = $keywordId;
	}

	function getEntryId(){
		return $this->entryId;
	}
	function setEntryId($entryId){
		$this->entryId = $entryId;
	}

	function getImportance(){
		return $this->importance;
	}
	function setImportance($importance){
		$this->importance = $this->importance;
	}
}
