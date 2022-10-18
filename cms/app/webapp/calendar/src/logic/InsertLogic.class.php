<?php
SOY2::Import("domain.SOYCalendar_ItemDAO");
class InsertLogic extends SOY2LogicBase{
	
	/**
	 * @param array, int<timestamp>, int, array<曜日の配列>
	 */
	function insertSchedules(array $item, int $endDate, int $cnt, array $ws){
		
	 	for($i = 0; $i < $cnt; $i++){
	 		$month = $item["month"] + $i;
	 		$year = $item["year"];
	 		if($month > 12){
	 			$month = $month-12;
	 			$year++;
	 		}
				 		
	 		$lastDate = soycalendar_get_last_date_timestamp($year, $month);
				 		
	 		//開始日の最終日と終了日を比較する。最終日よりも終了日が大きい場合はその月の最後までインサートする。
	 		$lastDay = ($endDate > $lastDate) ? $item["day"] : date("j", $endDate);
						
			//2週目以降のループは0からスタートする。
			$day = ($i == 0) ? $item["day"] : 1;
						
			for($j = $day; $j <= $lastDay; $j++){
				$schedule = soycalendar_get_schedule($year, $month, $j);	
				if($schedule > $lastDate) continue;
				
				//曜日のチェック
				if(is_bool(array_search(date("D", $schedule), $ws))) continue;

				self::_insertComplete($item, $schedule);
			}
	 	}
	}

	/**
	 * @param array, int<timestamp>
	 */
	function insertComplete(array $item, int $schedule){
		self::_insertComplete($item, $schedule);
	}

	/**
	 * @param array, int<timestamp>
	 */
	private function _insertComplete(array $item, int $schedule){
		$dao = self::_dao();
		
		$item = SOY2::cast("SOYCalendar_Item",$item);
		$item->setScheduleDate($schedule);
		
		try{
			$dao->insert($item);
		}catch(Exception $e){
			//var_dump($e);
		}
	}

	private function _dao(){
		static $d;
		if(is_null($d)) $d = SOY2DAOFactory::create("SOYCalendar_ItemDAO");
		return $d;
	}
}
