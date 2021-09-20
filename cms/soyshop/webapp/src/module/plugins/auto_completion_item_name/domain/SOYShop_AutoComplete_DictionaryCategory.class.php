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
		return $this->categoryId;
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
