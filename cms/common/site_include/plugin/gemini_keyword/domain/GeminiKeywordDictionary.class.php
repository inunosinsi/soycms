<?php

/**
 * @table GeminiKeywordDictionary
 */
class GeminiKeywordDictionary {

	/**
	 * @id
	 */
	private $id;
	private $keyword;


	function getId(){
		return $this->id;
	}
	function setId($id){
		$this->id = $id;
	}

	function getKeyword(){
		return $this->keyword;
	}
	function setKeyword($keyword){
		$this->keyword = $keyword;
	}
}
