<?php
/**
 * @table soycalendar_title
 */
class SOYCalendar_Title {
	
	/**
     * @id
	 */
	private $id;
	private $title;
	private $attribute;
	
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
	
	function getAttribute(){
		return $this->attribute;
	}
	function setAttribute($attribute){
		$this->attribute = $attribute;
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