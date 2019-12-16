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
		return $this->itemId;
	}
	function setItemId($itemId){
		$this->itemId = $itemId;
	}

	function getCategoryId(){
		return $this->categoryId;
	}
	function setCategoryId($categoryId){
		$this->categoryId = $categoryId;
	}

	function getUserId(){
		return $this->userId;
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
