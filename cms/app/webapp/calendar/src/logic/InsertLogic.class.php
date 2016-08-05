<?php
SOY2::Import("domain.SOYCalendar_ItemDAO");
class InsertLogic extends SOY2LogicBase{

	private $itemDao;
	
	function insertSchedule($item,$end,$endDate,$count,$flag){
		
	 	for($i=0;$i<$count;$i++){
	 		$month = $item["month"]+$i;
	 		$year = $item["year"];
	 		if($month > 12){
	 			$month = $month-12;
	 			$year = $year+1;
	 		}
				 		
	 		$lastDate = $this->getLastDate($month,$year);
				 		
	 		//開始日の最終日と終了日を比較する。最終日よりも終了日が大きい場合はその月の最後までインサートする。
	 		$lastDay = ($endDate > $lastDate)?substr($lastDate,6,2):$end["day"];
						
			//2週目以降のループは0からスタートする。
			$day = ($i == 0)?$item["day"]:1;
						
			for($j=$day;$j<=$lastDay;$j++){
				$schedule = $this->getSchedule($year,$month,$j);
				$getTimeStamp = $this->getTimeStamp($year,$month,$j);
				
				if($schedule > $lastDate)continue;
				
				//曜日のチェック
				if(in_array(date("D",$getTimeStamp),$flag)){
					$this->insertComplete($item,$schedule);
				}
				
			}
	 	}
	}

	function insertComplete($item,$schedule){
		if(!$this->itemDao)$this->itemDao = SOY2DAOFactory::create("domain.SOYCalendar_ItemDAO");
		$itemDao = $this->itemDao;
		
		$item = SOY2::cast("SOYCalendar_Item",$item);
		$item->setSchedule($schedule);
		$item->setCreateDate(time());
		$item->setUpdateDate(time());
		
		try{
			$itemDao->insert($item);
		}catch(Exception $e){
			var_dump($e);
		}
	}
	
	function getLastDate($month,$year){
		
		//12月対策
		if($month == 12){
			$year = $year+1;
			$month = 1;
		}else{
			$month = $month+1;
		}
		
		return date("Ymd",mktime(0,0,0,$month,0,$year));

	}
	
	function getSchedule($year,$month,$day){
		if(strlen($month)==1)$month = "0".$month;
		if(strlen($day)==1)$day = "0".$day;
		return $year.$month.$day;
	}
	
	function getTimeStamp($year,$month,$day){
		return mktime(0,0,0,$month,$day,$year);
	}
	
	
}
?>