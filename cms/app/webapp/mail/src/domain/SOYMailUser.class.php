<?php
/**
 * @table soymail_user
 */
class SOYMailUser {
	
	/* 削除フラグ */
	const USER_NOT_DISABLED = 0;	//アクティブユーザ
	const USER_IS_DISABLED = 1;		//削除されたユーザ
	
	const USER_NO_ERROR = 0;		
	const USER_IS_ERROR = 1;		//エラーが出たユーザ
	
	const USER_SEND = 0;
	const USER_NOT_SEND = 1;		//送信しないユーザ
	
	/**
	 * @id
	 */
	private $id;
	
	/**
	 * @column mail_address
	 */
	private $mailAddress;
	
	private $attribute1;
	private $attribute2;
	private $attribute3;
	
	private $name;
	private $reading;
	
	private $gender;
	
	private $birthday;
	
	/**
	 * @column zip_code
	 */
	private $zipCode;
	
	private $area;
	private $address1;
	private $address2;
	
	/**
	 * @column telephone_number
	 */
	private $telephoneNumber;
	
	/**
	 * @column fax_number
	 */
	private $faxNumber;
	
	/**
	 * @column cellphone_number
	 */
	private $cellphoneNumber;
	
	/**
	 * @column job_name
	 */
	private $jobName;
	
	/**
	 * @column job_zip_code
	 */
	private $jobZipCode;
	
	/**
	 * @column job_area
	 */
	private $jobArea;
	
	/**
	 * @column job_address1
	 */
	private $jobAddress1;
	
	/**
	 * @column job_address2
	 */
	private $jobAddress2;
	
	/**
	 * @column job_telephone_number
	 */
	private $jobTelephoneNumber;
	
	/**
	 * @column job_fax_number
	 */
	private $jobFaxNumber;
	
	private $memo;
	
	/**
	 * @column mail_error_count
	 */
	private $mailErrorCount = 0;
	
	/**
	 * @column not_send
	 */
	private $notSend = 0;
	
	/**
	 * @column is_error
	 */
	private $isError = 0;
	
	/**
	 * @column is_disabled
	 */
	private $isDisabled = 0;
	
	/**
	 * @column register_date
	 */
	private $registerDate;
	
	/**
	 * @column update_date
	 */
	private $updateDate;
	
	function getId() {
		return $this->id;
	}
	function setId($id) {
		$this->id = $id;
	}
	function getAttribute1() {
		return $this->attribute1;
	}
	function setAttribute1($attribute1) {
		$this->attribute1 = $attribute1;
	}
	function getAttribute2() {
		return $this->attribute2;
	}
	function setAttribute2($attribute2) {
		$this->attribute2 = $attribute2;
	}
	function getAttribute3() {
		return $this->attribute3;
	}
	function setAttribute3($attribute3) {
		$this->attribute3 = $attribute3;
	}
	function getName() {
		return $this->name;
	}
	function setName($name) {
		$this->name = $name;
	}
	function getReading() {
		return $this->reading;
	}
	function setReading($reading) {
		$this->reading = $reading;
	}
	function getGender() {
		return $this->gender;
	}
	function setGender($gender) {
		$this->gender = $gender;
	}
	function getBirthday() {
		return $this->birthday;
	}
	function setBirthday($birthday) {
		$this->birthday = $birthday;
	}
	function getZipCode() {
		return $this->zipCode;
	}
	function setZipCode($zipCode) {
		$this->zipCode = $zipCode;
	}
	function getArea() {
		return $this->area;
	}
	function setArea($area) {
		$this->area = $area;
	}
	function getAddress1() {
		return $this->address1;
	}
	function setAddress1($address1) {
		$this->address1 = $address1;
	}
	function getAddress2() {
		return $this->address2;
	}
	function setAddress2($address2) {
		$this->address2 = $address2;
	}
	function getTelephoneNumber() {
		return $this->telephoneNumber;
	}
	function setTelephoneNumber($telephoneNumber) {
		$this->telephoneNumber = $telephoneNumber;
	}
	function getFaxNumber() {
		return $this->faxNumber;
	}
	function setFaxNumber($faxNumber) {
		$this->faxNumber = $faxNumber;
	}
	function getCellphoneNumber() {
		return $this->cellphoneNumber;
	}
	function setCellphoneNumber($cellphoneNumber) {
		$this->cellphoneNumber = $cellphoneNumber;
	}
	function getJobName() {
		return $this->jobName;
	}
	function setJobName($jobName) {
		$this->jobName = $jobName;
	}
	function getJobZipCode() {
		return $this->jobZipCode;
	}
	function setJobZipCode($jobZipCode) {
		$this->jobZipCode = $jobZipCode;
	}
	function getJobArea() {
		return $this->jobArea;
	}
	function setJobArea($jobArea) {
		$this->jobArea = $jobArea;
	}
	function getJobAddress1() {
		return $this->jobAddress1;
	}
	function setJobAddress1($jobAddress1) {
		$this->jobAddress1 = $jobAddress1;
	}
	function getJobAddress2() {
		return $this->jobAddress2;
	}
	function setJobAddress2($jobAddress2) {
		$this->jobAddress2 = $jobAddress2;
	}
	function getJobTelephoneNumber() {
		return $this->jobTelephoneNumber;
	}
	function setJobTelephoneNumber($jobTelephoneNumber) {
		$this->jobTelephoneNumber = $jobTelephoneNumber;
	}
	function getJobFaxNumber() {
		return $this->jobFaxNumber;
	}
	function setJobFaxNumber($jobFaxNumber) {
		$this->jobFaxNumber = $jobFaxNumber;
	}
	function getMemo() {
		return $this->memo;
	}
	function setMemo($memo) {
		$this->memo = $memo;
	}
	function getMailErrorCount() {
		return $this->mailErrorCount;
	}
	function setMailErrorCount($mailErrorCount) {
		$this->mailErrorCount = $mailErrorCount;
	}
	function getNotSend(){
		return $this->notSend;
	}
	function setNotSend($notSend){
		$this->notSend = $notSend;
	}
	function getIsError(){
		return $this->isError;
	}
	function setIsError($isError){
		$this->isError = $isError;
	}
	function getIsDisabled() {
		return $this->isDisabled;
	}
	function setIsDisabled($isDisabled) {
		$this->isDisabled = $isDisabled;
	}

	function getMailAddress() {
		return $this->mailAddress;
	}
	function setMailAddress($mailAddress) {
		$this->mailAddress = $mailAddress;
	}

	function getRegisterDate() {
		return $this->registerDate;
	}
	function setRegisterDate($registerDate) {
		$this->registerDate = $registerDate;
	}
	function getUpdateDate() {
		return $this->updateDate;
	}
	function setUpdateDate($updateDate) {
		$this->updateDate = $updateDate;
	}
}
?>