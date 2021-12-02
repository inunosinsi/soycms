<?php
/**
 * @table soyshop_notepad
 */
class SOYShop_Notepad {

	/**
	 * @id
	 */
	private $id;

	/**
	 * @column item_id
	 */
	private $itemId;

	/**
	 * @column category_id
	 */
	private $categoryId;

	/**
	 * @column user_id
	 */
	private $userId;
	private $title;
	private $content;

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

	function getItemId(){
		return (is_numeric($this->itemId)) ? (int)$this->itemId : 0;
	}
	function setItemId($itemId){
		$this->itemId = $itemId;
	}

	function getCategoryId(){
		return (is_numeric($this->categoryId)) ? (int)$this->categoryId : 0;
	}
	function setCategoryId($categoryId){
		$this->categoryId = $categoryId;
	}

	function getUserId(){
		return (is_numeric($this->userId)) ? (int)$this->userId : 0;
	}
	function setUserId($userId){
		$this->userId = $userId;
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
