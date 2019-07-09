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
		return $this->name;
	}
	function setName($name){
		$this->name = $name;
	}

	function getMailAddress(){
		return $this->mailAddress;
	}
	function setMailAddress($mailAddress){
		$this->mailAddress = $mailAddress;
	}
}
