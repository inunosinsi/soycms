<?php
/**
 * @table soylpo_log
 */
class SOYLpo_Log {
	
	/**
	 * @column lpo_id
	 */
	private $lpoId;
	private $referer;
	
	/**
	 * @column entry_date
	 */
	private $entryDate;
	
	/**
	 * @column create_date
	 */
	private $createDate;

	function getLpoId(){
		return $this->lpoId;
	}
	function setLpoId($lpoId){
		$this->lpoId = $lpoId;
	}
	
	function getReferer(){
		return $this->referer;
	}
	function setReferer($referer){
		$this->referer = $referer;
	}
	
	function getEntryDate(){
		return $this->entryDate;
	}
	function setEntryDate($entryDate){
		$this->entryDate = $entryDate;
	}
	
	function getCreateDate(){
		return $this->createDate;
	}
	function setCreateDate($createDate){
		$this->createDate = $createDate;
	}
}
?>