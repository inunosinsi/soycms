<?php
/**
 * @table soylist_category
 */
class SOYList_Category {

	/**
	 * @id
	 */
	private $id;
	
	private $name;
	private $memo;
	private $sort;
	
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
	
	function getName(){
		return $this->name;
	}
	function setName($name){
		$this->name = $name;
	}
	
	function getMemo(){
		return $this->memo;
	}
	function setMemo($memo){
		$this->memo = $memo;
	}
	
	function getSort(){
		return $this->sort;
	}
	function setSort($sort){
		$this->sort = $sort;
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