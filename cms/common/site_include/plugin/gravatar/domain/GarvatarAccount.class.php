<?php

/**
 * @table GravatarAccount
 */
class GravatarAccount {

	/**
	 * @id
	 */
	private $id;
	private $name;

	/**
	 * @column mail_address
	 */
	private $mailAddress;

	function getId(){
		return $this->id;
	}
	function setId($id){
		$this->id = $id;
	}

	function getName(){
		return (is_string($this->name)) ? $this->name : "";
	}
	function setName($name){
		$this->name = $name;
	}

	function getMailAddress(){
		return (is_string($this->mailAddress)) ? $this->mailAddress : "";
	}
	function setMailAddress($mailAddress){
		$this->mailAddress = $mailAddress;
	}
}
