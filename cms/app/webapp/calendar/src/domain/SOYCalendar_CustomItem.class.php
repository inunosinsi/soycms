<?php
/**
 * @table soycalendar_custom_item
 */
class SOYCalendar_CustomItem {
	
	/**
     * @id
	 */
	private $id;
	private $label;
	private $alias;
	
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
	
	function getLabel(){
		return $this->label;
	}
	function setLabel($label){
		$this->label = $label;
	}
	
	function getAlias(){
		return $this->alias;
	}
	function setAlias($alias){
		$this->alias = $alias;
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