<?php
/**
 * @table soyshop_auto_complete_dictionary
 */
class SOYShop_AutoComplete_Dictionary {

	/**
	 * @column item_id
	 */
	private $itemId;
	private $hiragana;
	private $katakana;
	private $other;

	function getItemId(){
		return (is_numeric($this->itemId)) ? (int)$this->itemId : 0;
	}
	function setItemId($itemId){
		$this->itemId = $itemId;
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
