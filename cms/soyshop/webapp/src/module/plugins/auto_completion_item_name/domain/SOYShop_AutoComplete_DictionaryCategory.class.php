<?php
/**
 * @table soyshop_auto_complete_dictionary_category
 */
class SOYShop_AutoComplete_DictionaryCategory {

	/**
	 * @column category_id
	 */
	private $categoryId;
	private $hiragana;
	private $katakana;
	private $other;

	function getCategoryId(){
		return (is_numeric($this->categoryId)) ? (int)$this->categoryId : 0;
	}
	function setCategoryId($categoryId){
		$this->categoryId = $categoryId;
	}

	function getHiragana(){
		return $this->hiragana;
	}
	function setHiragana($hiragana){
		$this->hiragana = $hiragana;
	}

	function getKatakana(){
		return $this->katakana;
	}
	function setkatakana($katakana){
		$this->katakana = $katakana;
	}

	function getOther(){
		return $this->other;
	}
	function setOther($other){
		$this->other = $other;
	}
}
