<?php

class RemoveLogic extends SOY2LogicBase{

	private $itemDao;

	/**
	 * @param int<timestamp>, int<timestamp>, int, int
	 */
    function removeSchedules(int $startDate, int $endDate, int $cnt, int $titleId=0){
    	for($i = 0; $i < $cnt; $i++){
	 		$month = date("n", $startDate) + $i;
	 		$year = date("Y", $startDate);
	 		if($month > 12){
	 			$month = $month-12;
	 			$year = $year+1;
	 		}
				 		
	 		$lastDate = soycalendar_get_last_date_timestamp($year, $month);
				 		
	 		//開始日の最終日と終了日を比較する。最終日よりも終了日が大きい場合はその月の最後までインサートする。
	 		$lastDay = ($endDate > $lastDate) ? date("j", $lastDate) : date("j", $endDate);
						
			//2週目以降のループは0からスタートする。
			$day = ($i == 0) ? date("j", $startDate) : 1;
						
			for($j = $day; $j <= $lastDay; $j++){
				$schedule = soycalendar_get_schedule($year, $month, $j);
				if($schedule > $lastDate) continue;				
				self::_removeComplete($schedule, $titleId);
			}
	 	}
    }

	/**
	 * @param int
	 * @return bool
	 */
	function remove(int $id){
		try{
			self::_dao()->deleteById($id);
		}catch(Exception $e){
			return false;
		}

		//カスタム項目の削除
		self::_removeCustomItemChecks($id);

		return true;
	}

	/**
	 * @param int<timestamp>, int
	 */
    private function _removeComplete(int $schedule, int $titleId=0){
		
		try{
			$items = self::_dao()->getByScheduleDate($schedule);
		}catch(Exception $e){
			$items = array();
		}
		
		if(count($items) > 0){
			foreach($items as $item){
				$id = $item->getId();
				
				//タイトルの指定有りの場合
				if($titleId > 0){
					try{
						self::_dao()->deleteByIdAndTitleId($id,$titleId);
					}catch(Exception $e){
						//
					}
				//タイトルの指定無しの場合
				}else{
					try{
						self::_dao()->deleteById($id);
					}catch(Exception $e){
						//
					}
				}

				// カスタム項目の削除
				self::_removeCustomItemChecks($id);
			}	
		}
	}

	/**
	 * @param int
	 */
	private function _removeCustomItemChecks(int $itemId){
		try{
			self::_customDao()->deleteByItemId($itemId);
		}catch(Exception $e){
			//
		}
	}

	private function _dao(){
		static $d;
		if(is_null($d)) $d = SOY2DAOFactory::create("SOYCalendar_ItemDAO");
		return $d;
	}

	private function _customDao(){
		static $d;
		if(is_null($d)) $d = SOY2DAOFactory::create("SOYCalendar_CustomItem_CheckedDAO");
		return $d;
	}
}