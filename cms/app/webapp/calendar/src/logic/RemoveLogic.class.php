<?php

class RemoveLogic extends SOY2LogicBase{

    function removeSchedule($start,$end,$count,$endDate,$title=null){
    	
    	for($i=0;$i<$count;$i++){
	 		$month = $start["month"]+$i;
	 		$year = $start["year"];
	 		if($month > 12){
	 			$month = $month-12;
	 			$year = $year+1;
	 		}
				 		
	 		$lastDate = $this->getLastDate($month,$year);
				 		
	 		//開始日の最終日と終了日を比較する。最終日よりも終了日が大きい場合はその月の最後までインサートする。
	 		$lastDay = ($endDate > $lastDate)?substr($lastDate,6,2):$end["day"];
						
			//2週目以降のループは0からスタートする。
			$day = ($i == 0)?$start["day"]:1;
						
			for($j=$day;$j<=$lastDay;$j++){
				$schedule = $this->getSchedule($year,$month,$j);
				$getTimeStamp = $this->getTimeStamp($year,$month,$j);
				
				if($schedule > $lastDate)continue;
				
				$this->removeComplete($schedule,$title);
	
			}
	 	}
    	
    }
    
    function removeComplete($schedule,$title=null){
		if(!$this->itemDao)$this->itemDao = SOY2DAOFactory::create("SOYCalendar_ItemDAO");
		$itemDao = $this->itemDao;
	
		try{
			$items = $itemDao->getBySchedule($schedule);
		}catch(Exception $e){
			$items = new SOYCalendar_Item();
		}
		
		if(count($items)>0){
			foreach($items as $item){
				$id = $item->getId();
				
				//タイトルの指定有りの場合
				if(!is_null($title) && (int)$title > 0){
					try{
						$itemDao->deleteByIdAndTitle($id,$title);
					}catch(Exception $e){
					}
				//タイトルの指定無しの場合
				}else{
					try{
						$itemDao->deleteById($id);
					}catch(Exception $e){
					
					}
				}	
			}	
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