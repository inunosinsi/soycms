<?php
/**
 * @table soymock_sample
 */
class SOYMock_Sample {
	
	/**
	 * @id
	 */
	private $id;
	private $name;
	private $description;
	
	/**
	 * @column create_date
	 */
	private $createDate;
	
	/**
	 * @column update_date
	 */
	private $updateDate;
	
	/**
	 * @no_persistent
	 */
	private $meno;
	
	function getId(){
		return $this->id;
	}
	function setId($id){
		$this->id = $id;
	}
	
	function getName(){
		return $this->name;
	}
	function setName($name){
		$this->name = $name;
	}
	
	function getDescription(){
		return $this->description;
	}
	function setDescription($description){
		$this->description = $description;
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
	
	function getMemo(){
		$memo = "";
		
		return $memo;
	}
}
?>