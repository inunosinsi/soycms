<?php

/**
 * @table soyshop_reserve_calendar_label
 */
class SOYShopReserveCalendar_Label {

	const DISPLAY_ORDER_MAX = 127;

	/**
	 * @id
	 */
	private $id;

	/**
	 * @column item_id
	 */
	private $itemId;
	private $label;

	/**
	 * @column display_order
	 */
	private $displayOrder = 127;

	function getId(){
		return $this->id;
	}
	function setId($id){
		$this->id = $id;
	}

	function getItemId(){
		return $this->itemId;
	}
	function setItemId($itemId){
		$this->itemId = $itemId;
	}

	function getLabel(){
		return $this->label;
	}
	function setLabel($label){
		$this->label = $label;
	}

	function getDisplayOrder(){
		return $this->displayOrder;
	}
	function setDisplayOrder($displayOrder){
		$this->displayOrder = $displayOrder;
	}
}
