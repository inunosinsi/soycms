<?php

/**
 * @table soyshop_auto_ranking
 */
class SOYShop_AutoRanking{
	
	/**
	 * @id
	 */
	private $id;
	private $content;
	
	/**
	 * @column start_date
	 */
	private $startDate;
	
	/**
	 * @column create_date
	 */
	private $createDate;
	
	function getId(){
		return $this->id;
	}
	function setId($id){
		$this->id = $id;
	}
	
	function getContent(){
		return $this->content;
	}
	function setContent($content){
		$this->content = $content;
	}
	
	function getStartDate(){
		return $this->startDate;
	}
	function setStartDate($startDate){
		$this->startDate = $startDate;
	}
	
	function getCreateDate(){
		return $this->createDate;
	}
	function setCreateDate($createDate){
		$this->createDate = $createDate;
	}
}
?>