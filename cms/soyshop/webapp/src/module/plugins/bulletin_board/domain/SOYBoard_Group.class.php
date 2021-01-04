<?php
/**
 * @table soyboard_group
 */
class SOYBoard_Group {

	const UPPER_LIMIT = 1000;

	/* 削除フラグ */
	const NOT_DISABLED = 0;	//アクティブグループ
	const IS_DISABLED = 1;		//削除されたグループ

	/**
	 * @id
	 */
	private $id;

	private $name;

	/**
	 * @column display_order
	 */
	private $displayOrder = self::UPPER_LIMIT;

	/**
	 * @column is_disabled
	 */
	private $isDisabled = self::NOT_DISABLED;

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

	function getDisplayOrder(){
		return $this->displayOrder;
	}
	function setDisplayOrder($displayOrder){
		$this->displayOrder = $displayOrder;
	}

	function getIsDisabled() {
		return $this->isDisabled;
	}
	function setIsDisabled($isDisabled) {
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
