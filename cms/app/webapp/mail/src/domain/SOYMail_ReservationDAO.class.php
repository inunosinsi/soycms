<?php

abstract class SOYMail_ReservationDAO extends SOY2DAO{
	
	/**
	 * @return id
	 * @trigger onInsert
	 */
    abstract function insert(SOYMail_Reservation $reservation);
    
    /**
     * @trigger onUpdate
     */
    abstract function update(SOYMail_Reservation $reservation);
	
	/**
	 * @return list
	 */
	abstract function get();
	
	/**
	 * @return object
	 */
	abstract function getById($id);
	
	/**
	 * @return list
	 * @order id DESC
	 */
	abstract function getByMailId($mailId);
	
	/**
	 * @return list
	 * @query mail_id = :mailId AND is_send != 1
	 * @order id ASC
	 */
	abstract function getByMailIdAndNoSend($mailId);
	
	/**
	 * @return list
	 */
	abstract function getByIsSend($isSend);
	
	/**
	 * @return list
	 * @query is_send != 1 AND schedule_date < :now
	 * @order reserve_date DESC
	 */
	abstract function getSatisfyMailConditions($now);
	
	/**
	 * @final
	 */
	function onInsert($query,$binds){
		
		$binds[":isSend"] = 0;
		$binds[":reserveDate"] = time();
		
		return array($query,$binds);
	}
	
	/**
	 * @final
	 */
	function onUpdate($query,$binds){
		
		//今のところ何もなし
		
		return array($query,$binds);
	}
}

?>