<?php

/**
 * @table soyshop_user_storage
 */
class SOYShop_UserStorage{

	/**
	 * @id
	 */
	private $id;

	/**
	 * @column user_id
	 */
	private $userId;

	/**
	 * @column file_name
	 */
	private $fileName;
	private $token;

	/**
	 * @column upload_date
	 */
	private $uploadDate;

	function getId(){
		return $this->id;
	}
	function setId($id){
		$this->id = $id;
	}

	function getUserId(){
		return $this->userId;
	}
	function setUserId($userId){
		$this->userId = $userId;
	}

	function getFileName(){
		return $this->fileName;
	}
	function setFileName($fileName){
		$this->fileName = $fileName;
	}

	function getToken(){
		return $this->token;
	}
	function setToken($token){
		$this->token = $token;
	}

	function getUploadDate(){
		return $this->uploadDate;
	}
	function setUploadDate($uploadDate){
		$this->uploadDate = $uploadDate;
	}
}
