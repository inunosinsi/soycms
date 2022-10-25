<?php

class CalendarLogic extends SOY2LogicBase{

	//今月である場合はフラグを立てる
	private $isThisMonth=false;

	//管理画面でのカレンダ表示
	private $isManagerMode=false;

	//余ったカラムに次の月の日付を表示
	private $isNextMonthDate=false;

	//祝日のクラス
	private $holiday;

	//カラムの日付を取得
	private $today;

	//一括ですべての予定を取得しておく	array(timestamp => array(SOYCalendar_Item...)...)
	private $schedules;

	//一括ですべてのタイトルを取得しておく	array(titleId => array(SOYCalendar_Title...)...)
	private $titles;

	//一括ですべてのカスタム項目を取得しておく
	private $customItemCheckedList;

	//一括ですべてのカスタム項目のクラスを取得しておく
	private $customItemClasses;
	

	/** ここからPCモード **/

	function getCalendar(int $year, int $month, bool $isManagerMode=false, bool $isNextMonthDate=false, int $designate=0){
		$this->isManagerMode = $isManagerMode;
		$this->isNextMonthDate = $isNextMonthDate;
		$this->isThisMonth = false;	//今月のカレンダーではない
		return self::_getCalendarCommon(mktime(0,0,0,$month,1,$year));
	}

	function getCurrentCalendar(bool $isManagerMode=false, bool $isNextMonthDate=false){
		$this->isManagerMode = $isManagerMode;
		$this->isNextMonthDate = $isNextMonthDate;
		$this->isThisMonth = true;	//今月のカレンダーを表示するためのフラグ
		return self::_getCalendarCommon(time());
	}

	function getPrevCalendar(bool $isManagerMode=false, bool $isNextMonthDate=false){
		$this->isManagerMode = $isManagerMode;
		$this->isNextMonthDate = $isNextMonthDate;
		$this->isThisMonth = false;	//今月のカレンダーではない

		$year = date("Y");
		$month = date("n");

		if($month !== "1"){
			$month--;
		}else{
			$year--;
			$month = 12;
		}
		return self::_getCalendarCommon(mktime(0,0,0,$month,1,$year));
	}

	/**
	 * @param bool, bool
	 * @return string
	 */
	function getNextCalendar(bool $isManagerMode=false, bool $isNextMonthDate=false){
		$this->isManagerMode = $isManagerMode;
		$this->isNextMonthDate = $isNextMonthDate;
		$this->isThisMonth = false;	//今月のカレンダーではない

		$year = date("Y");
		$month = date("n");

		if($month !== "12"){
			$month++;
		}else{
			$year++;
			$month = 1;
		}
		return self::_getCalendarCommon(mktime(0,0,0,$month,1,$year));
	}

	//自由に表示したい月を指定できるカレンダ
	function getSpecifyCalendar(bool $isManagerMode=false, bool $isNextMonthDate=false, int $specify=0){
		$this->isManagerMode = $isManagerMode;
		$this->isNextMonthDate = $isNextMonthDate;

		$year = date("Y");
		$month = date("n")+(int)$specify;

		if($month > 12){
			$year++;
			$month = (int)$month - 12;
		}

		//今月であればtrueにしたい
		//$this->isThisMonth = false;
		
		return self::_getCalendarCommon(mktime(0,0,0,$month,1,$year));
	}

	/**
	 * @param int<timestamp>
	 * @return string
	 */
	private function _getCalendarCommon(int $timestamp=0){
		if($timestamp === 0) $timestamp = time();
		self::_setSchedulesAndTitlesBulkAcquisition($timestamp);
		if(!$this->isManagerMode){	//公開側の時のみ実行
			self::_setCustomItemCheckedListBulkAcquisition();
			self::_setCustomItemClassListBulkAcquisition();
		}
		$this->holiday = self::_exeGoogleCalendarDataAPI($timestamp);
		return self::_createCalendar($timestamp);
	}

	/**
	 * 任意の年月で予定を一括で取得しておく
	 * @param int<timestamp>
	 * @return void
	 */
	private function _setSchedulesAndTitlesBulkAcquisition(int $timestamp){
		list($first, $last) = soycalendar_get_first_date_or_last_date_timestamp($timestamp);
		try{
			$items = self::_itemDao()->getItemsFromFirstDateToLastDate($first, $last);
		}catch(Exception $e){
			$items = array();
		}

		if(count($items)){
			$titleIds = array();
			foreach($items as $item){
				if(!isset($this->schedules[$item->getScheduleDate()])) $this->schedules[$item->getScheduleDate()] = array();
				if(isset($this->schedules[$item->getScheduleDate()][$item->getId()])) continue;	// 二回同じ値を入れることを防止する
				$this->schedules[$item->getScheduleDate()][$item->getId()] = $item;
				if(is_bool(array_search($item->getTitleId(), $titleIds)) && !isset($this->titles[$item->getTitleId()])) $titleIds[] = $item->getTitleId();
			}

			if(count($titleIds)){
				try{
					$titles = self::_titleDao()->getByIds($titleIds);
				}catch(Exception $e){
					$titles = array();
				}
				if(count($titles)){
					foreach($titles as $title){
						if(isset($titles[$title->getId()])) continue;
						$titles[$title->getId()] = $title;
					}
				}
			}
		}
	}

	/**
	 * 任意の年月でカスタム項目を一括で取得しておく
	 * @return void
	 */
	private function _setCustomItemCheckedListBulkAcquisition(){
		if(!is_array($this->schedules) || !count($this->schedules)) return;
		
		$itemIds = array();
		foreach($this->schedules as $schs){
			if(!count($schs)) continue;
			foreach($schs as $itemId => $_sch){
				$itemIds[] = $itemId;
			}
		}

		if(!count($itemIds)) return;
		
		$results = self::_chkDao()->getCheckedListByItemIds($itemIds);
		if(!count($results)) return;
		
		foreach($results as $itemId => $res){
			if(!count($res) || isset($this->customItemCheckedList[$itemId])) continue;
			$this->customItemCheckedList[$itemId] = $res;
		}
	}

	/**
	 * 任意の年月でカスタム項目のクラスを一括で取得しておく
	 * @return void
	 */
	private function _setCustomItemClassListBulkAcquisition(){
		if(!is_array($this->customItemCheckedList) || !count($this->customItemCheckedList)) return;

		$customIds = array();
		foreach($this->customItemCheckedList as $arr){
			if(!count($arr)) continue;
			foreach($arr as $customId){
				if(count($customIds) && is_numeric(array_search($customId, $customIds))) continue;
				$customIds[] = $customId;
			}
		}
		if(!count($customIds)) return;

		$results = self::_cusDao()->getClassListByIds($customIds);
		if(!count($results)) return;

		foreach($results as $customId => $alias){
			if(isset($this->customItemClasses[$customId])) continue;

			$alias = trim($alias);
			if(!strlen($alias)) continue;

			$this->customItemClasses[$customId] = $alias;
		}
	}

	/**
	 * @param int<timestamp>
	 * @return array
	 */
	private function _exeGoogleCalendarDataAPI(int $timestamp){
		if(!function_exists("simplexml_load_file")) return array();
		$year = date("Y", $timestamp);
		$month = date("n", $timestamp);

		$start = $year."-".$month."-01";
		$end = $year."-".$month."-31";

		$xml = @simplexml_load_file("http://www.google.com/calendar/feeds/japanese@holiday.calendar.google.com/public/full" . "?start-min=" . $start . "&start-max=" . $end . "&max-results=10");
		return ($xml instanceof SimpleXMLElement) ? self::_holiday($xml) : array();
	}

	/**
	 * @param SimpleXMLElement
	 * @return array
	 */
	private function _holiday(SimpleXMLElement $xml){
		if(!isset($xml->entry)) return array();

	    $arr = array();
		foreach($xml->entry as $entry){
			$gd = $entry->children( "http://schemas.google.com/g/2005" );

			// $gd->when[ 'startTime' ] だと何故か null が返るので、属性を検索する
			foreach($gd->when->attributes() as $name => $v){
				if($name != "startTime") continue;
				$arr[] = str_replace("-","",(string)$v);
				break;
			}
		}
		return $arr;
	}

	/**
	 * @param int<timestamp>
	 * @return string
	 */
	private function _createCalendar(int $timestamp){
		
		//その月の日付の数
		$num = date("t",$timestamp);

		//表示用の年月日を取得
		$year = date("Y",$timestamp);
		$month = date("n",$timestamp);
		$day = date("j",$timestamp);

		//カレンダを表示する
		$html = array();
		if($this->isManagerMode){
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
		for($i = 1; $i <= $num; $i++){
			//本日の曜日を取得する
			$this->today = mktime(0, 0, 0, $month, $i, $year);
			$w = date("w",$this->today);	//曜日

			//その月の初日
			if($i == 1){
				$html[] = "<tr>";

				//初日まで（前月）
				for($j = 1; $j <= $w; $j++){
					if($this->isNextMonthDate){
						$lastDate = date("j",soycalendar_get_last_date_timestamp($year,$month-1));	//先月の最終日を取得
						$int = $lastDate - $w + $j;
						$html[] = self::_createDayColumn($int,0,0,0,true,$timestamp);
					}else{
						$html[] = "<td>&nbsp;</td>";
					}

					$counter++;
				}

				$html[] = self::_createDayColumn($i,$w,$day,$num);
				$counter++;
				//土曜日の場合は</tr>で閉じる
				if($w == 6){
					$html[] = "</tr>";
				}
			//二日目以降
			}else{
				if($w == 0){
					$html[] = "<tr>";
				}
				$html[] = self::_createDayColumn($i,$w,$day,$num);
				$counter++;

				//末日以降（次の月）
				if($i == $num && $w < 6){
					$count = 7 - $counter;
					for($k = 1; $k <= $count; $k++){
						$html[] = ($this->isNextMonthDate) ? self::_createDayColumn($k,0,0,0,true,$timestamp) : "<td>&nbsp;</td>";
					}
				}

				if($w == 6){
					$html[] = "</tr>";
					$counter = 0;
				}
			}
		}
		$html[] = "</tbody>";
		$html[] = "</table>";

		return implode("\n",$html);
	}

	/**
	 * @int, int, int, int, bool, int<timestamp>
	 * @return string
	 */
	private function _createDayColumn(int $i, int $w, int $day, int $num, bool $isNextMonth=false, int $timestamp=0){
		
		$todayTime = $this->today;

		//指定のクラス名
		$attribute = (!$isNextMonth) ? self::_getTitleAttribute($todayTime) : "";

		if($isNextMonth && $timestamp > 0){
			if($i < 7){
				$todayTime = self::getNextMonthDate($i, $timestamp);
			}else{
				$todayTime = self::getPrevMonthDate($i, $timestamp);
			}

			$w = date("w",$todayTime);
		}

		$isToday = ($this->isThisMonth && $i == $day);

		$classes = array();
		switch($w){
			case 0:
				$classes[] = "sun";
				break;
			case 6:
				$classes[] = "sat";
				break;
			default:
				//
		}

		//今日より前ならbeforeを追加
		if((strtotime("+1day", $todayTime)) < time()) $classes[] = "before";

		if($isToday) $classes[] = "today";
		if(in_array(date("Ymd",$todayTime),$this->holiday)) $classes[] = "holiday";
		if($isNextMonth) $classes[] = " other";
		if(isset($attribute)) $classes[] = $attribute;

		$html = array();
		$html[] = (count($classes)) ? "<td class=\"" . implode(" ", $classes) . "\">" : "<td>";
		$html[] = self::_displaySchedule($i, $todayTime, $isNextMonth);
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
		$arr = array();

		//現在のページ
		$pageId = (isset($_GET["page"])) ? $_GET["page"] : 1;

		//表示したい日数
		$count = 12;

		//今日の日付 次のページ以降は1日後
		$today = ($pageId > 1) ? strtotime("+1day", time()) * $count * ($pageId - 1) : time();

		for($i = 0; $i < $count; $i++){
			$date = strtotime("+".$i."day", $today);
			$arr[] = array("content" => self::displaySchedule(date("d",$today),$date,false,true));
		}

		return $arr;
	}

	/**
	 * 予定を表示する
	 * @param int, int<timestamp>, bool, bool
	 * @return string
	 */
	private function _displaySchedule(int $int, int $timestamp, bool $monthFlag=false, bool $isMobile=false){
		$schedules = (isset($this->schedules[$timestamp])) ? $this->schedules[$timestamp] : array();
		$ynjw = soycalendar_get_Ynjw($timestamp);

		$html = array();

		// 日付数字に<span class='num'>を追加
		$html[] = "<span class=\"num\">";

		if($this->isManagerMode) $html[] = "<a href=\"".$_SERVER["SCRIPT_NAME"]."/calendar/Schedule/Detail?year=".$ynjw["year"]."&month=".$ynjw["month"]."&day=".$ynjw["day"]."\">";

		//モバイルモードの表示
		if($isMobile){
			//日曜の場合
			switch($ynjw["week"]){
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
			$html[] = "<span class=\"".$class."\">".$ynjw["year"]."/".$ynjw["month"]."/".$ynjw["day"]."(".$this->weekText[$ynjw["week"]].")</span>\n";
		//PCモードの表示
		}else{
			$html[] = $int;
		}

		if($this->isManagerMode) $html[] = "</a>\n";

		// 日付数字用<span class='num'>の閉じタグを追加
		$html[] = "</span>";

		$html[] = "<br />\n";

		if(count($schedules)){
			$html[] = "<span class=\"schedule\">\n";

			foreach($schedules as $key => $schedule){
				if(!is_numeric($schedule->getTitleId())) continue;
				
				//まずはタイトルを取得
				if($key > 0) $html[] = "<br />";

				$titles = CalendarAppUtil::getTitleList();
				
				if($this->isManagerMode) $html[] = "<a href=\"".$_SERVER["SCRIPT_NAME"]."/calendar/Schedule/Detail/".$schedule->getId()."\">";

				$html[] = "<span class=\"" . implode(" ", self::_getScheduleClassList($schedule->getId(), "title")) . "\">";
				$html[] = $titles[$schedule->getTitleId()];
				$html[] = "</span>\n";
				if($isMobile) $html[] = "&nbsp;";
				if(strlen($schedule->getStart()) > 0){
					$html[] = "<span class=\"" . implode(" ", self::_getScheduleClassList($schedule->getId(), "content")) . "\">";
					$html[] = htmlspecialchars($schedule->getStart(), ENT_QUOTES,"UTF-8");
					if(strlen($schedule->getEnd() ) >0){
						$html[] = "～".htmlspecialchars($schedule->getEnd(),ENT_QUOTES,"UTF-8");
					}
					$html[] = "</span>\n";
				}
				
				if($this->isManagerMode)$html[] = "</a>";
			}

			$html[] = "</span>\n";
		}

		//if(!$mobile) $html[] = "</td>";


		return implode("",$html);
	}

	/**
	 * @param int, string
	 * @return array
	 */
	private function _getScheduleClassList(int $itemId, string $col="title"){
		$list = array($col);
		if(!isset($this->customItemCheckedList[$itemId]) || !is_array($this->customItemCheckedList[$itemId]) || !count($this->customItemCheckedList[$itemId])) return $list;

		foreach($this->customItemCheckedList[$itemId] as $customId){
			if(!isset($this->customItemClasses[$customId])) continue;
			$list[] = $this->customItemClasses[$customId];
		}

		return $list;
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

	/**
	 * タイムスタンプから年、月、日の値を取得する
	 * @param int<timestamp>
	 * @return array
	 */
	private function _ynj(int $timestamp){
		$array = array();
		$array["year"] = date("Y",$timestamp);
		$array["month"] = date("n",$timestamp);
		$array["day"] = date("j",$timestamp);
		//曜日
		$array["week"] = date("w",$timestamp);
		return $array;
	}

	private function _getTitleAttribute(int $timestamp){
		if(!is_array($this->titles) || !count($this->titles) || !isset($this->schedules[$timestamp])) return "";
		$schedules = $this->schedules[$timestamp];
		if(!count($schedules)) return "";

		$attr = "";
		$counter = 0;
		foreach($schedules as $schedule){
			if(!is_numeric($schedule->getTitleId())) continue;
			
			$attrV = $this->titles[$schedule->getTitleId()]->getAttribute();
			if($counter > 0){
				if(!preg_match("/".$attrV."/",$attr)){
					$attr .= " ".$attrV;
				}
			}else{
				$attr = $attrV;
			}
			$counter++;
		}

		return $attr;
	}

	/**
	 * @param int, int<timestamp>
	 * @return int<timestamp>
	 */
	private function getNextMonthDate(int $i, int $time){
		$year = date("Y",$time);
		$month = date("n",$time)+1;

		if($month == 13){
			$year = $year+1;
			$month = 1;
		}
		return mktime(0,0,0,$month,$i,$year);
	}

	/**
	 * @param int, int<timestamp>
	 * @return int<timestamp>
	 */
	private function getPrevMonthDate(int $i,int $time){
		$year = date("Y",$time);
		$month = date("n",$time)-1;

		if($month == 0){
			$year = $year-1;
			$month = 12;
		}
		return mktime(0,0,0,$month,$i,$year);
	}

	/** ここからページャに関するもの **/

	/**
	 * @param string
	 * @return string
	 */
	function getPrevPager(string $path){
		$pageId = (isset($_GET["page"]) && is_numeric($_GET["page"]) && (int)$_GET["page"] > 1) ? $_GET["page"] - 1 : null;
		if(isset($pageId)) $path = $path . "?page=" . $pageId;
		return $path;
	}

	/**
	 * @return bool
	 */
	function isPrev(){
		return (isset($_GET["page"]) && (int)$_GET["page"] > 1);
	}

	/**
	 * @param string
	 * @return string
	 */
	function getNextPager(string $path){
		$pageId = (isset($_GET["page"])) ? $_GET["page"] + 1 : 2;
		return $path . "?page=" . $pageId;
	}

	private function _itemDao(){
		static $d;
		if(is_null($d)) $d = SOY2DAOFactory::create("SOYCalendar_ItemDAO");
		return $d;
	}

	private function _titleDao(){
		static $d;
		if(is_null($d)) $d = SOY2DAOFactory::create("SOYCalendar_TitleDAO");
		return $d;
	}

	private function _cusDao(){
		static $d;
		if(is_null($d)) $d = SOY2DAOFactory::create("SOYCalendar_CustomItemDAO");
		return $d;
	}

	private function _chkDao(){
		static $d;
		if(is_null($d)) $d = SOY2DAOFactory::create("SOYCalendar_CustomItem_CheckedDAO");
		return $d;
	}
}
