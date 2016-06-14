<?php

/**
 * @table soyshop_campaign
 */
class SOYShop_Campaign {
	
	const PERIOD_START = 0;
	const PERIOD_END = 2147483647;
	
	const IS_OPEN = 1;
	const NO_OPEN = 0;
	
	const IS_DISABLED = 1;
	const NO_DISABLED = 0;
	
	/**
	 * @id
	 */
	private $id;
	private $title;
	private $content;
	
	/**
	 * @column post_period_start
	 */
	private $postPeriodStart = 0;
	
	/**
	 * @column post_period_end
	 */
	private $postPeriodEnd = 2147483647;
	
	/**
	 * @column is_open
	 */
	private $isOpen = 0;
	
	/**
	 * @column is_disabled
	 */
	private $isDisabled = 0;
	
	/**
	 * @column create_date
	 */
	private $createDate;
	
	/**
	 * @column update_date
	 */
	private $updateDate;
	
	function getId(){
		return $this->id;
	}
	function setId($id){
		$this->id = $id;
	}
	
	function getTitle(){
		return $this->title;
	}
	function setTitle($title){
		$this->title = $title;
	}
	
	function getContent(){
		return $this->content;
	}
	function setContent($content){
		$this->content = $content;
	}
	
	function getPostPeriodStart(){
		return $this->postPeriodStart;
	}
	function setPostPeriodStart($postPeriodStart){
		$this->postPeriodStart = $postPeriodStart;
	}
	
	function getPostPeriodEnd(){
		return $this->postPeriodEnd;
	}
	function setPostPeriodEnd($postPeriodEnd){
		$this->postPeriodEnd = $postPeriodEnd;
	}
	
	function getIsOpen(){
		return $this->isOpen;
	}
	function setIsOpen($isOpen){
		$this->isOpen = $isOpen;
	}
	
	function getIsDisabled(){
		return $this->isDisabled;
	}
	function setIsDisabled($isDisabled){
		$this->isDisabled($isDisabled);
	}
	
	function getCreateDate(){
		return $this->createDate;
	}
	function setCreateDate($createDate){
		$this->createDate = $createDate;
	}
	
	function getUpdateDate(){
		return $this->updateDate;
	}
	function setUpdateDate($updateDate){
		$this->updateDate = $updateDate;
	}
}
?>