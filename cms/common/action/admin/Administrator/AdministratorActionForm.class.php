<?php
class AdministratorActionForm extends SOY2ActionForm{
	var $id;
	var $userId;
	var $password;
	var $name;
	var $email;

	function setId($value){
		$this->id = $value;
	}

	/**
     * @validator string {"max" : 30, "min" : 4, "require" : true }
     */
	function setUserId($value){
		$this->userId = $value;
	}

	/**
     * @validator string {"max" : 30, "min" : 6, "require" : true }
     */
	function setPassword($value){
		$this->password = $value;
	}

	/**
     * @validator string {"max" : 255, "min" : 0}
     */
	function setName($value){
		$this->name = $value;
	}

	/**
     * @validator string {"max" : 255, "min" : 0}
     */
	function setEmail($value){
		$this->email = $value;
	}
}
