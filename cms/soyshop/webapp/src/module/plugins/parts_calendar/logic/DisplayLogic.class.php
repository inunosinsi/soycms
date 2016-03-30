<?php

class DisplayLogic extends SOY2LogicBase{
	
	function DisplayLogic(){
		SOY2::import("module.plugins.parts_calendar.common.PartsCalendarCommon");
	}

	function getCurrentCalendar(){

		//今月のカレンダーを表示するためのフラグ
		$flag = true;

		//今日の日付を取得する
		$time = time();

		return $this->createCalendar($flag,$time);
	}

	function getNextCalendar(){

		//来月のカレンダーを表示するためのフラグ
		$flag = false;

		$time = time();

		$year = date("Y",$time);
		$month = date("n",$time);

		if($month !== "12"){
			$month = $month + 1;
		}else{
			$year = $year + 1;
			$month = 1;
		}

		$time = mktime(0,0,0,$month,1,$year);

		return $this->createCalendar($flag,$time);
	}

	function createCalendar($flag,$time){

		//その月の日付の数（最後の日）：28-31
		$num = date("t",$time);

		//表示用の年月日を取得
		$year = date("Y",$time);
		$month = date("n",$time);
		$day = date("j",$time);

		//カレンダを表示する
		$html = array();
		$html[] = "<table>";
		$html[] = "<caption>" . $year."年" . $month."月の営業日</caption>";
		$html[] = "<thead>";
		$html[] = "<tr>";
		$html[] = "<th class=\"sun\">日</th>\n<th>月</th>\n<th>火</th>\n<th>水</th>\n<th>木</th>\n<th>金</th>\n<th class=\"sat\">土</th>";
		$html[] = "</tr>";
		$html[] = "</thead>";

		$html[] = "<tbody>";

		//<tr>から</tr>までの<td>のカウンター
		$counter = 0;
		//カレンダの日付を作成する
		for($i=1;$i<=$num;$i++){
			//本日の曜日を取得する
			$getToday = mktime(0,0,0,$month,$i,$year);
			$getDay = date("w",$getToday);

			//その月の初日
			if($i == 1){
				$html[] = "<tr>";
				for($j=1;$j<=$getDay;$j++){
					$html[] = "<td>&nbsp;</td>";
					$counter++;
				}
				$html[] = $this->createDayColumn($i,$getDay,$day,$num,$flag,$getToday);
				$counter++;
				//土曜日の場合は</tr>で閉じる
				if($getDay == 6){
					$html[] = "</tr>";
				}
			//二日目以降
			}else{
				if($getDay == 0){
					$html[] = "<tr>";
				}
				$html[] = $this->createDayColumn($i,$getDay,$day,$num,$flag,$getToday);
				$counter++;
				if($getDay == 6 || $i == $num){
					if($counter !== 6){
						$count = 7 - $counter;
						for($k=0;$k<$count;$k++)$html[] = "<td>&nbsp;</td>";
						$counter = 0;
					}
					$html[] = "</tr>";
				}
			}
		}
		$html[] = "</tbody>";
		$html[] = "</table>";

		return implode("\n", $html);
	}

	/**
	 * 指定した日のセルを返す
	 * @param Integer 日
	 * @param Integer 週の何日目か
	 * @param Integer 今現在の日付
	 * @param Integer その月の最後の日
	 * @param Integer 今月かどうか
	 * @param Integer タイムスタンプ
	 * @return Text
	 */
	function createDayColumn($i,$w,$day,$num,$thisMonth,$dateTime){

		$today = false;
		if($i == $day && $thisMonth == true) $today = true;

		$class = array();

		//今日かどうか
		if($today == true){
			$class[] = "today";
		}
		
		$calendarLogic = SOY2Logic::createInstance("module.plugins.parts_calendar.logic.CalendarLogic");

		//休日
		if(!$calendarLogic->isBD($dateTime)){
			$class[] = "close";
		}

		//その他
		if($calendarLogic->isOther($dateTime)){
			$class[] = "other";
		}

		if($w == 0){
			$class[] = "sun";
		}elseif($w == 6){
			$class[] = "sat";
		}

		$attr = count($class) ? ' class="'.implode(" ",$class).'"' : '' ;

		return "<td{$attr}>" . $i."</td>";
	}
}
?>