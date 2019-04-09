<?php

/**
 * @table soyshop_reserve_calendar_schedule
 */
class SOYShopReserveCalendar_Schedule {

	/**
	 * @id
	 */
	private $id;

	/**
	 * @column item_id
	 */
	private $itemId;

	/**
	 * @column label_id
	 */
	private $labelId;

	private $price;
	private $year;
	private $month;
	private $day;

	/**
	 * @column unsold_seat
	 */
	private $unsoldSeat;

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

	function getLabelId(){
		return $this->labelId;
	}
	function setLabelId($labelId){
		$this->labelId = $labelId;
	}

	function getPrice(){
		return $this->price;
	}
	function setPrice($price){
		$this->price = $price;
	}

	function getYear(){
		return $this->year;
	}
	function setYear($year){
		$this->year = $year;
	}

	function getMonth(){
		return $this->month;
	}
	function setMonth($month){
		$this->month = $month;
	}

	function getDay(){
		return $this->day;
	}
	function setDay($day){
		$this->day = $day;
	}

	function getUnsoldSeat(){
		return $this->unsoldSeat;
	}
	function setUnsoldSeat($unsoldSeat){
		$this->unsoldSeat = $unsoldSeat;
	}
}
