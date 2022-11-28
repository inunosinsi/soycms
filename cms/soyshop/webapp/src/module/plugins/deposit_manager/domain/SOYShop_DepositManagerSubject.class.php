<?php
/**
 * @table soyshop_deposit_manager_subject
 */
class SOYShop_DepositManagerSubject {

	const DISPLAY_ORDER_LIMIT = 127;

	/**
	 * @id
	 */
	private $id;
	private $subject;

	/**
	 * @column display_order
	 */
	private $displayOrder = 0;

	function getId(){
		return $this->id;
	}
	function setId($id){
		$this->id = $id;
	}

	function getSubject(){
		return $this->subject;
	}
	function setSubject($subject){
		$this->subject = $subject;
	}

	function getDisplayOrder(){
		return $this->displayOrder;
	}
	function setDisplayOrder($displayOrder){
		$this->displayOrder = $displayOrder;
	}
}
