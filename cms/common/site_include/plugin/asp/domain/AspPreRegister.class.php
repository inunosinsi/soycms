<?php

/**
 * @table asp_pre_register
 */
class AspPreRegister {

	private $token;

	/**
	 * @column site_id
	 */
	private $siteId;
	private $data;

	/**
	 * @column create_date
	 */
	private $createDate;

	function getToken(){
		return $this->token;
	}
	function setToken($token){
		$this->token = $token;
	}

	function getSiteId(){
		return $this->siteId;
	}
	function setSiteId($siteId){
		$this->siteId = $siteId;
	}

	function getData(){
		return $this->data;
	}
	function setData($data){
		$this->data = $data;
	}

	function getDataArray(){
		return soy2_unserialize($this->data);
	}
	function setDataArray($array){
		//パスワードはハッシュで保持
		if(strpos($array["userPassword"], "sha512/") === false || strpos($array["userPassword"], "sha512/") !== 0){
			SOY2::import("util.PasswordUtil");
			$array["userPassword"] = PasswordUtil::hashPassword($array["userPassword"]);
		}
		$this->data = soy2_serialize($array);
	}

	function getCreateDate(){
		return $this->createDate;
	}
	function setCreateDate($createDate){
		$this->createDate = $createDate;
	}
}
