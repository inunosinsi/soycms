<?php

/**
 * @table ReadEntryCount
 */
class ReadEntryCount {

	/**
	 * @column entry_id
	 */
	private $entryId;

	/**
		* @column count
		*/
	private $count = 0;

	function getEntryId(){
		return $this->entryId;
	}
	function setEntryId($entryId){
		$this->entryId = $entryId;
	}

	function getCount(){
		return $this->count;
	}
	function setCount($count){
		$this->count = $count;
	}
}
