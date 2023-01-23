<?php
/**
 * @table soycalendar_item
 */
class SOYCalendar_Item {

	/**
	 * @id
	 */
	private $id;

	/**
	 * @column schedule_date
	 */
	private $scheduleDate;

	/**
	 * @column title_id
	 */
	private $titleId;
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
	
	function getScheduleDate(){
		return $this->scheduleDate;
	}
	function setScheduleDate($scheduleDate){
		$this->scheduleDate = $scheduleDate;
	}
	
	function getTitleId(){
		return $this->titleId;
	}
	function setTitleId($titleId){
		$this->titleId = $titleId;
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
