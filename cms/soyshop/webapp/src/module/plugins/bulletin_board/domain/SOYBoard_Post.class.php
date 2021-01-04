<?php
/**
 * @table soyboard_post
 */
class SOYBoard_Post {

	const IS_OPEN = 1;
	const NO_OPEN = 0;

	/**
	 * @id
	 */
	private $id;

	/**
	 * @column topic_id
	 */
	private $topicId;

	/**
	 * @column user_id
	 */
	private $userId;

	private $content;

	/**
	 * @column is_open
	 */
	private $isOpen = self::NO_OPEN;

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

	function getTopicId(){
		return $this->topicId;
	}
	function setTopicId($topicId){
		$this->topicId = $topicId;
	}

	function getUserId(){
		return $this->userId;
	}
	function setUserId($userId){
		$this->userId = $userId;
	}

	function getContent(){
		return $this->content;
	}
	function setContent($content){
		$this->content = $content;
	}

	function getIsOpen(){
		return $this->isOpen;
	}
	function setIsOpen($isOpen){
		$this->isOpen = $isOpen;
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
