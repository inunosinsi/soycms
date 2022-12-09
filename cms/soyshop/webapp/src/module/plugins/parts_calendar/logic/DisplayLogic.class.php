<?php

class DisplayLogic extends SOY2LogicBase{
	
	function __construct(){
		SOY2::import("module.plugins.parts_calendar.common.PartsCalendarCommon");
	}

	function getCurrentCalendar(){
		return self::_build(true, time());
	}

	function getNextCalendar(){
		$year = date("Y");
		$month = date("n");

		if($month !== "12"){
			$month++;
		}else{
			$year++;
			$month = 1;
		}

		return self::_build(false, mktime(0,0,0,$month,1,$year));
	}

	/**
	 * @param bool flag(来月を表示するフラグ), int timestamp
	 * @return html
	 */
	private function _build(bool $flag, int $timestamp){
		//キャッシュファイルがあればキャッシュから
		if(self::_checkIsCacheFile($timestamp)) return self::_readCacheFile($timestamp);

		//その月の日付の数（最後の日）：28-31
		$num = date("t", $timestamp);

		//表示用の年月日を取得
		$year = date("Y", $timestamp);
		$month = date("n", $timestamp);
		$day = date("j", $timestamp);

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
		for($i = 1; $i <= $num; $i++){
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
				$html[] = self::_createDayColumn($i,$getDay,$day,$num,$flag,$getToday);
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
				$html[] = self::_createDayColumn($i,$getDay,$day,$num,$flag,$getToday);
				$counter++;
				if($getDay == 6 || $i == $num){
					if($counter !== 6){
						$count = 7 - $counter;
						for($k = 0; $k < $count; $k++) $html[] = "<td>&nbsp;</td>";
						$counter = 0;
					}
					$html[] = "</tr>";
				}
			}
		}
		$html[] = "</tbody>";
		$html[] = "</table>";

		$html = implode("\n", $html);

		//キャッシュを残す
		self::_createCache($html, $timestamp);

		return $html;
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
	private function _createDayColumn(int $i,int $w, int $day, int $num, int $thisMonth, int $dateTime){

		$today = false;
		if($i == $day && $thisMonth == true) $today = true;

		$class = array();

		//今日かどうか
		if($today == true){
			$class[] = "today";
		}
		
		//休日
		if(!self::_logic()->isBD($dateTime)){
			$class[] = "close";
		}

		//その他
		if(self::_logic()->isOther($dateTime)){
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

	private function _createCache(string $html, int $timestamp){
		file_put_contents(self::_cacheDir() . date("Ymd", $timestamp) . ".html", $html);
	}

	private function _checkIsCacheFile(int $timestamp){
		//古いキャッシュは削除する
		self::_removeCacheFiles();
		
		$cacheFile = self::_cacheDir() . date("Ymd", $timestamp) . ".html";
		return (file_exists($cacheFile));
	}

	private function _readCacheFile($timestamp){
		return file_get_contents(self::_cacheDir() . date("Ymd", $timestamp) . ".html");
	}

	function removeCacheFiles(bool $isAll=false){
		self::_removeCacheFiles($isAll);
	}

	private function _removeCacheFiles(bool $isAll=false){
		$cacheDir = self::_cacheDir();
		$files = soy2_scandir($cacheDir);

		//キャッシュファイルをどれくらい残すか？
		$cnt = ($isAll) ? 3 : 0;	// $isAllがtrueの場合はすべて削除
		if(count($files) >= $cnt){
			$t = ($isAll) ? time() : soyshop_shape_timestamp(strtotime("-1 day"), "end");
			foreach($files as $file){
				if(filemtime($cacheDir . $file) < $t) unlink($cacheDir . $file);
			}
		}
	}

	private function _cacheDir(){
		$dir = SOYSHOP_SITE_DIRECTORY . ".cache/calendar/";
		if(!file_exists($dir)) mkdir($dir);
		return $dir;
	}

	private function _logic(){
		static $l;
		if(is_null($l)) $l = SOY2Logic::createInstance("module.plugins.parts_calendar.logic.CalendarLogic");
		return $l;
	}
}