<?php
/**
 * @table soyboard_topic
 */
class SOYBoard_Topic {

	/**
	 * @id
	 */
	private $id;

	/**
	 * @column group_id
	 */
	private $groupId;

	private $label;

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

	function getGroupId(){
		return $this->groupId;
	}
	function setGroupId($groupId){
		$this->groupId = $groupId;
	}

	function getLabel(){
		return $this->label;
	}
	function setLabel($label){
		$this->label = $label;
	}

	function getCreateDate(){
		return $this->createDate;
	}
	function setCreateDate($createDate){
		$this->createDate = $createDate;
	}
}
