<?php

/**
 * @table GeminiKeyword
 */
class GeminiKeyword {

	/**
	 * @id
	 */
	private $id;

	/**
	 * @column keyword_id
	 */
	private $keywordId;

	/**
	 * @column hiragana_id
	 */
	private $hiraganaId;

	/**
	 * @column katakana_id
	 */
	private $katakanaId;

	function getId(){
		return $this->id;
	}
	function setId($id){
		$this->id = $id;
	}

	function getKeywordId(){
		return $this->keywordId;
	}
	function setKeywordId($keywordId){
		$this->keywordId = $keywordId;
	}

	function getHiraganaId(){
		return $this->hiraganaId;
	}
	function setHiraganaId($hiraganaId){
		$this->hiraganaId = $hiraganaId;
	}

	function getKatakanaId(){
		return $this->katakanaId;
	}
	function setKatakanaId($katakanaId){
		$this->katakanaId = $katakanaId;
	}
}
