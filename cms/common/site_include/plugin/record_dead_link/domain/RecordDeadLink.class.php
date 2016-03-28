<?php
/**
 * @table RecordDeadLink
 */
class RecordDeadLink{
	
	/**
	 * @id
	 */
	private $id;
	private $referer;
	private $url;
	
	/**
	 * @column register_date
	 */
	private $registerDate;
	
	function getId(){
		return $this->id;
	}
	function setId($id){
		$this->id = $id;
	}
	
	function getReferer(){
		return $this->referer;
	}
	function setReferer($referer){
		$this->referer = $referer;
	}
	
	function getUrl(){
		return $this->url;
	}
	function setUrl($url){
		$this->url = $url;
	}
	
	function getRegisterDate(){
		return $this->registerDate;
	}
	function setRegisterDate($registerDate){
		$this->registerDate = $registerDate;
	}
}
?>