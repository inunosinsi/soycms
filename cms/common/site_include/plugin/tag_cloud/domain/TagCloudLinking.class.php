<?php
/**
 * @table TagCloudLinking
 */
class TagCloudLinking {

	/**
	 * @column entry_id
	 */
	private $entryId;

	/**
	 * @column word_id
	 */
	private $wordId;

	function getEntryId(){
		return $this->entryId;
	}
	function setEntryId($entryId){
		$this->entryId = $entryId;
	}

	function getWordId(){
		return $this->wordId;
	}
	function setWordId($wordId){
		$this->wordId = $wordId;
	}
}
