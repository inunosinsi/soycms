<?php

class CronLogic extends SOY2LogicBase{
	
	private $reservationDao;
	
	/**
	 * 予約テーブルにある条件を満たした配列を返す
	 * @return array mailId, offset
	 * 
	 */
	function getMailReservations(){
		
		$reservationDao = $this->getDao();
		
		try{
			//予約中のオブジェクトから送信条件を満たすものだけを取得
			$reservations = $reservationDao->getSatisfyMailConditions(time());
		}catch(Exception $e){
			return array();
		}
		
		$array = array();
		
		foreach($reservations as $reservation){
			if(!is_null($reservation->getMailId()) && is_numeric($reservation->getMailId())){
				$values = array();
				$values["mailId"] = $reservation->getMailId();
				$values["offset"] = (int)$reservation->getOffset();
				
				$array[] = $values;
			}
		}
		
		return $array;
	}
	
	/**
	 * 送信済みフラグを立てる
	 * @param INT mailId
	 */
	function update($mailId){
		
		$reservationDao = $this->getDao();
		
		try{
			$reservations = $reservationDao->getByMailIdAndNoSend($mailId);
		}catch(Exception $e){
			return false;
		}
		
		//cronの分割送信の際、前回と今回のデータが取得できるけど、古い方のものだけupdateする
		$reservation = array_shift($reservations);
		
		//送信済みフラグと送信時間を記録する
		$reservation->setIsSend(SOYMail_Reservation::IS_SEND);
		$reservation->setSendDate(time());
		
		try{
			$reservationDao->update($reservation);
		}catch(Exception $e){
			//
		}
	}
	
	function getDao(){
		if(!$this->reservationDao){
			$this->reservationDao = SOY2DAOFactory::create("SOYMail_ReservationDAO");
		}
		return $this->reservationDao;
	}
}

?>