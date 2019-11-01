<?php

class CalendarLogic extends SOY2LogicBase{

	private $itemDao;
	private $titleDao;

	//今月である場合はフラグを立てる
	private $flag;

	//管理画面でのカレンダ表示
	private $manager;

	//余ったカラムに次の月の日付を表示
	private $nextMonthDate;

	//祝日のクラス
	private $holiday;

	//カラムの日付を取得
	private $today;

	/** ここからPCモード **/

	function getCalendar($year,$month,$manager=false,$nextMonthDate=false,$designate=0){

		$this->manager = $manager;
		$this->nextMonthDate = $nextMonthDate;
		$this->flag = false;

		$this->holiday = self::getGoogleCalendarDataAPI($year,$month);

		$time = mktime(0,0,0,$month,1,$year);

		return self::createCalendar($time);
	}

	function getCurrentCalendar($manager=false,$nextMonthDate=false){

		$this->manager = $manager;
		$this->nextMonthDate = $nextMonthDate;

		//今月のカレンダーを表示するためのフラグ
		$this->flag = true;

		//今日の日付を取得する
		$time = time();

		$this->holiday = self::getGoogleCalendarDataAPI(date("Y",$time),date("m",$time));

		return self::createCalendar($time);

	}

	function getPrevCalendar($manager=false,$nextMonthDate=false){
		$this->manager;
		$this->nextMonthDate = $nextMonthDate;

		//先月のカレンダーを表示するためのフラグ
		$this->flag = false;

		$time = time();

		$year = date("Y",$time);
		$month = date("n",$time);

		if($month !== "1"){
			$month = $month - 1;
		}else{
			$year = $year - 1;
			$month = 12;
		}

		$time = mktime(0,0,0,$month,1,$year);

		$this->holiday = self::getGoogleCalendarDataAPI($year,$month);

		return self::createCalendar($time);
	}

	function getNextCalendar($manager=false,$nextMonthDate=false){

		$this->manager;
		$this->nextMonthDate = $nextMonthDate;

		//来月のカレンダーを表示するためのフラグ
		$this->flag = false;

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

		$this->holiday = self::getGoogleCalendarDataAPI($year,$month);

		return self::createCalendar($time);

	}

	//自由に表示したい月を指定できるカレンダ
	function getSpecifyCalendar($manager=false,$nextMonthDate=false,$specify=0){

		$this->manager;
		$this->nextMonthDate = $nextMonthDate;

		//来月のカレンダーを表示するためのフラグ
		$this->flag = false;

		$time = time();

		$year = date("Y",$time);
		$month = date("n",$time);

		$month = $month+(int)$specify;

		if($month > 12){
			$year = $year + 1;
			$month = (int)$month - 12;
		}

		$time = mktime(0,0,0,$month,1,$year);

		$this->holiday = self::getGoogleCalendarDataAPI($year,$month);

		return self::createCalendar($time);

	}

	private function getGoogleCalendarDataAPI($year,$month){
		if(!function_exists("simplexml_load_file")) return array();

		$start = $year."-".$month."-01";
		$end = $year."-".$month."-31";

		$query =  "start-min=" . $start . "&start-max=" . $end . "&max-results=10";
		$feed = "http://www.google.com/calendar/feeds/japanese@holiday.calendar.google.com/public/full" . "?" . $query;

		$xml = @simplexml_load_file($feed);

		return self::getHoliday($xml);
	}

	private function getHoliday($xml){

	    $array = array();

		if(isset($xml->entry)){
			foreach($xml->entry as $entry){
	        	$gd = $entry->children( "http://schemas.google.com/g/2005" );
	        	$attributes = $gd->when->attributes();

	        	// $gd->when[ 'startTime' ] だと何故か null が返るので、属性を検索する
	        	foreach($attributes as $name => $value){
	          		if($name == "startTime"){
	                	$value = (string)$value;
	                	$array[] = str_replace("-","",$value);
	                	break;
	            	}
	        	}
		    }
		}
    	return $array;
	}


	private function createCalendar($time){

		$manager = $this->manager;

		//その月の日付の数
		$num = date("t",$time);

		//表示用の年月日を取得
		$year = date("Y",$time);
		$month = date("n",$time);
		$day = date("j",$time);

		//カレンダを表示する
		$html = array();
		if($manager == true){
			$html[] = "<table class=\"calendar_table\">";
		}else{
			$html[] = "<table>";
		}
		$html[] = "<caption>".$year."年".$month."月</caption>";
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
			$this->today = mktime(0,0,0,$month,$i,$year);
			$getDay = date("w",$this->today);

			//その月の初日
			if($i == 1){
				$html[] = "<tr>";

				//初日まで（前月）
				for($j=1;$j<=$getDay;$j++){
					if($this->nextMonthDate==true){
						$lastDate = date("j",self::getPrevMonthLastDate($month,$year));
						$int = $lastDate-$getDay+$j;
						$html[] = self::createDayColumn($int,0,0,0,true,$time);
					}else{
						$html[] = "<td>&nbsp;</td>";
					}

					$counter++;
				}

				$html[] = self::createDayColumn($i,$getDay,$day,$num);
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
				$html[] = self::createDayColumn($i,$getDay,$day,$num);
				$counter++;

				//末日以降（次の月）
				if($i == $num&&$getDay < 6){
					$count = 7 - $counter;
					for($k=1;$k<=$count;$k++){
						if($this->nextMonthDate == true){
							$html[] = self::createDayColumn($k,0,0,0,true,$time);
						}else{
							$html[] = "<td>&nbsp;</td>";
						}
					}
				}

				if($getDay == 6){
					$html[] = "</tr>";
					$counter = 0;
				}
			}
		}
		$html[] = "</tbody>";
		$html[] = "</table>";

		return implode("\n",$html);

	}

	private function createDayColumn($i,$w,$day,$num,$nextMonth=false,$time=null){

		$flag = $this->flag;
		$todayTime = $this->today;
		$holiday = $this->holiday;

		//指定のクラス名
		$attribute = ($nextMonth===false) ? self::getTitleAttribute($todayTime) : null;

		if($nextMonth==true){
			if($i<7){
				$todayTime = self::getNextMonthDate($i,$time);
			}else{
				$todayTime = self::getPrevMonthDate($i,$time);
			}

			$w = date("w",$todayTime);
		}

		$today = false;
		if($i == $day && $flag == true) $today = true;

		$classes = array();
		if($w == 0) {
			$classes[] = "sun";
		}else if($w == 6){
			$classes[] = "sat";
		}

		//今日より前ならbeforeを追加
		if(($todayTime + 24 * 60 * 60) < time()) {
			$classes[] = "before";
		}

		if($today){
			$classes[] = "today";
		}
		if(in_array(date("Ymd",$todayTime),$holiday)){
			$classes[] = "holiday";
		}
		if($nextMonth == true){
			$classes[] = " other";
		}

		if(isset($attribute)){
			$classes[] = $attribute;
		}

		$html = array();
		$html[] = (count($classes)) ? "<td class=\"" . implode(" ", $classes) . "\">" : "<td>";
		$html[] = self::displaySchedule($i,$todayTime,$nextMonth);
		$html[] = "</td>";
		return implode("",$html);
	}

	/** ここからモバイルモード **/


	/** 共通パーツ **/

	/**
	 * @return 17日分のデータが格納された配列
	 * @index schedule
	 */
	function getMobileCalendar(){

		$array = array();

		//現在のページ
		$pageId = (isset($_GET["page"])) ? $_GET["page"] : 1;

		//今日の日付
		$today = time();

		//表示したい日数
		$count = 12;

		$add1Day = 60*60*24;

		if($pageId>1){
			$today = $today + $add1Day * $count * ($pageId - 1);
		}

		for($i=0;$i<$count;$i++){
			$obj = array();
			$date = $today + $add1Day*$i;
			$obj["content"] = self::displaySchedule(date("d",$today),$date,false,true);
			$array[] = $obj;
		}

		return $array;
	}

	//予定を表示する
	function displaySchedule($int,$dateTime,$monthFlag=false,$mobile=false){

		$manager = $this->manager;

		$Ynj = $this->getYnj($dateTime);
		$schedules = self::getSchedule($dateTime);

		$html = array();

		// 日付数字に<span class='num'>を追加
		$html[] = "<span class=\"num\">";

		if($manager == true) $html[] = "<a href=\"".$_SERVER["SCRIPT_NAME"]."/calendar/Create?year=".$Ynj["year"]."&month=".$Ynj["month"]."&day=".$Ynj["day"]."\">";

		//モバイルモードの表示
		if($mobile){
			//日曜の場合
			switch($Ynj["week"]){
				//日曜の場合
				case 0:
					$class = "date sun";
					break;
				case 6:
					$class = "date sat";
					break;
				default:
					$class = "date";
					break;
			}
			$html[] = "<span class=\"".$class."\">".$Ynj["year"]."/".$Ynj["month"]."/".$Ynj["day"]."(".$this->weekText[$Ynj["week"]].")</span>\n";
		//PCモードの表示
		}else{
			$html[] = $int;
		}

		if($manager == true) $html[] = "</a>\n";

		// 日付数字用<span class='num'>の閉じタグを追加
		$html[] = "</span>";

		$html[] = "<br />\n";

		if(count($schedules)>0){

			$html[] = "<span class=\"schedule\">\n";

			foreach($schedules as $key => $schedule){
				//まずはタイトルを取得
				if($key>0)$html[] = "<br />";

				$titles = self::getTitleArray();

				if($manager == true)$html[] = "<a href=\"".$_SERVER["SCRIPT_NAME"]."/calendar/Detail/".$schedule->getId()."\">";
				$html[] = "<span class=\"title\">";
				$html[] = $titles[$schedule->getTitle()];
				$html[] = "</span>\n";
				if($mobile) $html[] = "&nbsp;";
				$html[] = "<span class=\"content\">";
				$html[] = htmlspecialchars($schedule->getStart(),ENT_QUOTES,"UTF-8");
				if(strlen($schedule->getEnd())>0){
					$html[] = "～".htmlspecialchars($schedule->getEnd(),ENT_QUOTES,"UTF-8");
				}
				$html[] = "</span>\n";
				if($manager == true)$html[] = "</a>";
			}

			$html[] = "</span>\n";
		}

		//if(!$mobile) $html[] = "</td>";


		return implode("",$html);
	}

	private function getPrevMonthLastDate($month,$year){
		return mktime(0,0,0,$month,0,$year);
	}

	//タイムスタンプから今日の日付を取得
	private function getTime($timestamp){
		return date("Ymd",$timestamp);
	}

	private $weekText = array(
		"0" => "日",
		"1" => "月",
		"2" => "火",
		"3" => "水",
		"4" => "木",
		"5" => "金",
		"6" => "土"
	);

	//タイムスタンプから年、月、日の値を取得する
	private function getYnj($timestamp){
		$array = array();
		$array["year"] = date("Y",$timestamp);
		$array["month"] = date("n",$timestamp);
		$array["day"] = date("j",$timestamp);
		//曜日
		$array["week"] = date("w",$timestamp);
		return $array;
	}

	private function getTitleAttribute($timestamp){

		$attribute = null;

		$schedules = self::getSchedule($timestamp);
		if(count($schedules)>0){
			$counter = 0;
			foreach($schedules as $schedule){
				$titleId = $schedule->getTitle();
				if(isset($titleId)){
					if(!$this->titleDao)$this->titleDao = SOY2DAOFactory::create("SOYCalendar_TitleDAO");
					$titleDao = $this->titleDao;
					try{
						$title = $titleDao->getById($titleId);
					}catch(Exception $e){
						$title = new SOYCalendar_Title();
					}

					$attr = $title->getAttribute();
					if($counter>0){
						if(!preg_match("/".$attr."/",$attribute)){
							$attribute .= " ".$attr;
						}
					}else{
						$attribute = $attr;
					}
					$counter++;

				}
			}
		}

		return $attribute;

	}

	//タイムスタンプからその日のスケジュールを取得する
	private function getSchedule($timestamp){
		if(!$this->itemDao)$this->itemDao = SOY2DAOFactory::create("SOYCalendar_ItemDAO");
		$itemDao = $this->itemDao;

		$time = $this->getTime($timestamp);
		try{
			$schedule = $itemDao->getBySchedule($time);
		}catch(Exception $e){
			$object = new SOYCalendar_Item();
			$schedule = array(0=>$object);
		}
		return $schedule;
	}

	private function getTitleArray(){
		$dao = SOY2DAOFactory::create("SOYCalendar_TitleDAO");
		$titles = $dao->get();

		$array = array();
		foreach($titles as $title){
			$array[$title->getId()] = $title->getTitle();
		}
		return $array;
	}

	private function getNextMonthDate($i,$time){
		$year = date("Y",$time);
		$month = date("n",$time)+1;

		if($month=="13"){
			$year = $year+1;
			$month = 1;
		}
		return mktime(0,0,0,$month,$i,$year);
	}
	private function getPrevMonthDate($i,$time){
		$year = date("Y",$time);
		$month = date("n",$time)-1;

		if($month=="0"){
			$year = $year-1;
			$month = 12;
		}
		return mktime(0,0,0,$month,$i,$year);
	}

	/** ここからページャに関するもの **/

	function getPrevPager($path){
		$pageId = (isset($_GET["page"])) ? $_GET["page"] - 1 : null;

		if(isset($pageId)){
			$path = $path . "?page=" . $pageId;
		}

		return $path;
	}

	function isPrev(){
		return (isset($_GET["page"])&&$_GET["page"]>1) ? true : false;
	}

	function getNextPager($path){
		if(isset($_GET["page"])){
			$pageId = $_GET["page"] + 1;
		}else{
			$pageId = 2;
		}

		return $path . "?page=" . $pageId;
	}
}
