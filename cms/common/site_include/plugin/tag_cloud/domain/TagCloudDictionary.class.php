<?php
/**
 * @table TagCloudDictionary
 */
class TagCloudDictionary {

	/**
	 * @id
	 */
	private $id;
	private $word;

	function getId(){
		return $this->id;
	}
	function setId($id){
		$this->id = $id;
	}

	function getWord(){
		return $this->word;
	}
	function setWord($word){
		$this->word = $word;
	}
}
