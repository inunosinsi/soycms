<?php
/**
 * @table soyshop_tag_cloud_linking
 */
class SOYShop_TagCloudLinking {

	/**
	 * @column item_id
	 */
	private $itemId;

	/**
	 * @column word_id
	 */
	private $wordId;

	function getItemId(){
		return $this->itemId;
	}
	function setItemId($itemId){
		$this->itemId = $itemId;
	}

	function getWordId(){
		return $this->wordId;
	}
	function setWordId($wordId){
		$this->wordId = $wordId;
	}
}
