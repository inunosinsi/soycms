<?php
/**
 * @table soyshop_coupon_category
 */
class SOYShop_CouponCategory {

	/**
	 * @id
	 */
	private $id;

	/**
	 * @column category_name
	 */
	private $name;

	/**
	 * @column coupon_code_prefix
	 */
	private $prefix;

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

	function getPrefix(){
		return $this->prefix;
	}
	function setPrefix($prefix){
		$this->prefix = $prefix;
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
	function setUpdateDate($UpdateDate){
		$this->updateDate = $UpdateDate;
	}
}
