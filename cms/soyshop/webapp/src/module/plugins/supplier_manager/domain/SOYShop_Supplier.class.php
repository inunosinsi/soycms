<?php
SOY2::import("domain.config.SOYShop_Area");
/**
 * @table soyshop_supplier
 */
class SOYShop_Supplier {

	/**
	 * @id
	 */
	private $id;
	private $name;

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
	 * @column mail_address
	 */
	private $mailAddress;
	private $url;

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

	function getName(){
		return $this->name;
	}
	function setName($name){
		$this->name = $name;
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
		if(!is_numeric($area))$area = SOYShop_Area::getArea($area);
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
	function getMailAddress(){
		return $this->mailAddress;
	}
	function setMailAddress($mailAddress){
		$this->mailAddress = $mailAddress;
	}
	function getUrl(){
		return $this->url;
	}
	function setUrl($url){
		$this->url = $url;
	}

	function getCreateDate(){
		return $this->createDate;
	}
	function setCreateDate($createDate){
		$this->createDate = $createDate;
	}

	function getUpdateDate(){
		return $this->$updateDate;
	}
	function setUpdateDate($updateDate){
		$this->updateDate = $updateDate;
	}
}
