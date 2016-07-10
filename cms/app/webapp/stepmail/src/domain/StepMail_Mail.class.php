<?php
/**
 * @table stepmail_mail
 */
class StepMail_Mail{
	
	const NO_DISABLED = 0;
	const IS_DISABLED = 1;
	
	/**
	 * @id
	 */
	private $id;
	
	/**
	 * @column mail_id
	 */
	private $mailId;
	private $title;
	private $overview;
	
	/**
	 * @column is_disabled
	 */
	private $isDisabled = 0;
	
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
	
	function getMailId(){
		return $this->mailId;
	}
	function setMailId($mailId){
		$this->mailId = $mailId;
	}
	
	function getTitle(){
		return $this->title;
	}
	function setTitle($title){
		$this->title = $title;
	}
	
	function getOverview(){
		return $this->overview;
	}
	function setOverview($overview){
		$this->overview = $overview;
	}
	
	function getIsDisabled(){
		return $this->isDisabled;
	}
	function setIsDisabled($isDisabled){
		$this->isDisabled = $isDisabled;
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