<?php
/**
 * @table soyshop_tag_cloud_language
 */
class SOYShop_TagCloudLanguage {

	/**
	 * @column word_id
	 */
	private $wordId;
	private $lang;
	private $label;

	function getWordId(){
		return $this->wordId;
	}
	function setWordId($wordId){
		$this->wordId = $wordId;
	}

	function getLang(){
		return $this->lang;
	}
	function setLang($lang){
		$this->lang = $lang;
	}

	function getLabel(){
		return $this->label;
	}
	function setLabel($label){
		$this->label = $label;
	}
}