<?php
/**
 * @table soycalendar_item
 */
class SOYCalendar_Item {

	/**
	 * @id
	 */
	private $id;
	private $schedule;
	private $title;
	private $start;
	private $end;
	
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
	
	function getSchedule(){
		return $this->schedule;
	}
	function setSchedule($schedule){
		$this->schedule = $schedule;
	}
	
	function getTitle(){
		return $this->title;
	}
	function setTitle($title){
		$this->title = $title;
	}
	
	function getStart(){
		return $this->start;
	}
	function setStart($start){
		$this->start = $start;
	}
	
	function getEnd(){
		return $this->end;
	}
	function setEnd($end){
		$this->end = $end;
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