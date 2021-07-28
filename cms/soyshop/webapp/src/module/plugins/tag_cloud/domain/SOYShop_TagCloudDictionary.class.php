<?php
/**
 * @table soyshop_tag_cloud_dictionary
 */
class SOYShop_TagCloudDictionary {

	/**
	 * @id
	 */
	private $id;
	private $word;
	private $hash;

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

	function getHash(){
		return $this->hash;
	}
	function setHash($hash){
		$this->hash = $hash;
	}
}
