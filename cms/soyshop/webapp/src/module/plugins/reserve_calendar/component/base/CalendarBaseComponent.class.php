<?php

class CalendarBaseComponent extends SOY2LogicBase{

	private $days = array("日", "月", "火", "水", "木", "金", "土");

	//祝日のクラス
	private $holidayList = array();

	//定休日のクラス
	private $regularHolidayList;

	//カラムの日付を取得
	private $cd;

	//何週目であるかを保持しておく
	private $wc;

	//翌月の日付を表示するか？
	private $dspOtherMonthDate;

	//captionを表示するか(年月)
	private $dspCation;

	//captionの両端に前の月等のリンクを出力
	private $dspMonthLink;

	//今月のカレンダーであるか？
	private $isThisMonth;

	//今日よりも前の週や日にbeforeクラスを付与
	private $isBefore;

	//来月のカレンダーを合わせて表示するか？
	private $isNextMonth;

	function __construct(){}

	function build($y, $m, $dspOtherMD = true, $dspCaption = true, $dspRegHol = false, $dspMonthLink = false, $isBefore = false, $isNextMonth = false){

		//週のカウントを初期化する
		$this->wc = 0;

		//本日の日付を取得
		if(!defined("TODAY_DATE")) define("TODAY_DATE", date("j", time()));
		if(!defined("TODAY_MONTH")) define("TODAY_MONTH", date("n", time()));

		//他の月を表示しない場合かつ日曜スタートでない場合は1を加算しておく
		if(!$dspOtherMD && date("w", mktime(0, 0, 0, $m, 1, $y)) != "0") $this->wc++;

		//翌月の日付を表示するか？
		$this->dspOtherMonthDate = $dspOtherMD;
		$this->isThisMonth = true;

		//今日よりも前の週や日にbeforeクラスを付与
		$this->isBefore = $isBefore;

		//次の月のカレンダーを続けて表示するか？
		$this->isNextMonth = $isNextMonth;

		//captionを表示するか
		$this->dspCaption = $dspCaption;
		$this->dspMonthLink = $dspMonthLink;

		//祭日を調べる
		self::useGoogleCalendarDataAPI($y, $m);

		//定休日を調べる
		if($dspRegHol) $this->getRegularHolidayList();
		return self::create(mktime(0, 0, 0, $m, 1, $y));
	}

	private function useGoogleCalendarDataAPI($y, $m){
/**
		$calendar_id = urlencode('japanese__ja@holiday.calendar.google.com');
		// 取得期間
		$start  = $y . "-" . self::convert($m++) . "-01T00:00:00Z";
		if($m > 12){
			$y++;
			$m = 1;
		}
		$end = $y . "-" . self::convert($m) . "-01T00:00:00Z";
		$url = 'https://www.google.com/calendar/feeds/' . $calendar_id . '/public/basic';
		$url .= '?start-min=' . $start;
		$url .= '&start-max=' . $end;
		$url .= '&max-results=10';
		$url .= '&alt=json';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$result = curl_exec($ch);
		curl_close($ch);

		//データが取得できなかった場合は結果のチェックを辞める
		if (empty($result)) return;
		$json = json_decode($result, true);
		if (empty($json['feed']['entry'])) return;

		foreach ($json['feed']['entry'] as $val) {
			$this->holidayList[] = preg_replace('#\A.*?(2\d{7})[^/]*\z#i', '$1', $val['id']['$t']);
		}
**/
	}

	//定休日を取得する
	function getRegularHolidayList(){}

	private function create($t){
		//その月の日付の数
		$last = date("t", $t);

		$nextMonthLast = ($this->isNextMonth) ? date("t", strtotime("+1 month", $t)) : 0;

		//表示用の年月日を取得
		$y = date("Y", $t);
		$m = date("n", $t);
		$d = date("j", $t);

		//カレンダを表示する
		$html = array();

		if(defined("RESERVE_CALENDAR_MODE")){
			switch(RESERVE_CALENDAR_MODE){
				case "bootstrap":
					//@ToDo モードを細分化するかもしれない
					$html[] = "<table class=\"table reserve_calendar\">";
					break;
				default:
					$html[] = "<table>";
			}
		}else{
			$html[] = "<table>";
		}

		if($this->dspCaption){
			$h = array();
			$h[] = "	<caption>";
			if($this->dspMonthLink){
				$prevY = ($m === 1) ? $y - 1 : $y;
				$prevM = ($m === 1) ? 12 : $m - 1;
				$h[] = "<a href=\"" . $_SERVER["REDIRECT_URL"] . "?y=" . $prevY . "&m=" . $prevM . "\">&lt;&lt;</a>";
			}
			$h[] = $y . "年" . $m . "月";
			if($this->dspMonthLink){
				$nextY = ($m === 12) ? $y + 1 : $y;
				$nextM = ($m === 12) ? 1 : $m + 1;
				$h[] = "<a href=\"" . $_SERVER["REDIRECT_URL"] . "?y=" . $nextY . "&m=" . $nextM . "\">&gt;&gt;</a>";
			}
			$h[] = "</caption>";
			$html[] = implode("\n", $h);
		}
		$html[] = "	<thead>";
		$html[] = "		<tr>";
		foreach(range(0,6) as $i){
			switch($i){
				case 0:	//日曜日
					$html[] = "			<th class=\"sun\">" . $this->days[$i] . "</th>";
					break;
				case 6:	//土曜日
					$html[] = "			<th class=\"sat\">" . $this->days[$i] . "</th>";
					break;
				default:
					$html[] = "			<th>" . $this->days[$i] . "</th>";
			}
		}
		$html[] = "		</tr>";
		$html[] = "	</thead>";

		$html[] = "	<tbody>";

		//<tr>から</tr>までの<td>のカウンター
		$counter = 0;

		$today = date("j");

		//カレンダの日付を作成する
		for($i = 1; $i <= $last + $nextMonthLast; ++$i){
			//次の月のカレンダー用に日付を書き換える
			if($i > $last){
				$ii = $i - $last;
				$mm = $m + 1;
				$tt = strtotime("+1 month", $t);	//月始めのタイムスタンプを更新
			}else{
				$ii = $i;
				$mm = $m;
				$tt = $t;
			}

			if($mm > 12){
				$mm -= 12;
				$yy = $y + 1;
			}else{
				$yy = $y;
			}

			//カラムごとの曜日を取得する
			$this->cd = mktime(0, 0, 0, $mm, $ii, $yy);
			$w = date("w", $this->cd);

			//その月の初日
			if($i === 1){
				$thisM = date("n");
				if($this->isBefore && TODAY_MONTH == $m && $i + (6 - $w) < $today){	//今日を含む週よりも前の週であればclassにbeforeを追加
					$html[] = "		<tr class=\"before\">";
				}else if($this->isNextMonth && TODAY_MONTH == $m && $i > $last){		//次の月のカレンダー
					$html[] = "		<tr class=\"next\">";
				}else{
					$html[] = "		<tr>";
				}

				//初日まで（前月）
				for($j = 1; $j <= $w; $j++){
					if($this->dspOtherMonthDate){
						$lastDate = date("j", self::getPrevMonthLastDate($mm, $yy));
						$int = $lastDate - $w + $j;
						$html[] = self::createDayColumn($int, 0, 0, 0, $last, true, $tt);
					}else{
						$html[] = "			<td class=\"empty\">&nbsp;</td>";
					}

					$counter++;
				}

				$html[] = self::createDayColumn($ii, $mm, $w, $d, $last);
				$counter++;
				//土曜日の場合は</tr>で閉じる
				if($w == 6){
					$html[] = "		</tr>";
				}
			//二日目以降
			}else{
				if($w == 0){
					if($this->isBefore && TODAY_MONTH == $m && $i + (6 - $w) < $today){	//今日を含む週よりも前の週であればclassにbeforeを追加
						$html[] = "		<tr class=\"before\">";
					}else if($this->isNextMonth && TODAY_MONTH == $m && $i > $last){		//次の月のカレンダー
						$html[] = "		<tr class=\"next\">";
					}else{
						$html[] = "		<tr>";
					}
				}
				$html[] = self::createDayColumn($ii, $mm, $w, $d, $last);
				$counter++;

				//末日以降（次の月）
				if($i == $last + $nextMonthLast && $w < 6){
					for($k = 1; $k <= 7 - $counter; $k++){
						if($this->dspOtherMonthDate){
							$html[] = self::createDayColumn($k, $mm, 0, 0, 0, true, $tt);
						}else{
							$html[] = "			<td class=\"empty\">&nbsp;</td>";
						}
					}
				}


				if($w == 6){
					$html[] = "		</tr>";
					$counter = 0;
				}
			}
		}
		$html[] = "	</tbody>";
		$html[] = "</table>";

		return implode("\n", $html);
	}

	private function createDayColumn($i, $m, $w, $d, $last, $isOtherMonth = false, $t = null){

		//定休日リスト
		$rhList = (isset($this->regularHolidayList[$m])) ? $this->regularHolidayList[$m] : array();

		//曜日の属性 土、日、祭日
		$da = "";

		if($isOtherMonth){
			if($i < 7){
				$this->cd = self::getNextMonthDate($i, $t);
			}else{
				$this->cd = self::getPrevMonthDate($i, $t);
			}

			$w = date("w", $this->cd);
		}

		$isToday = ($this->isThisMonth && $i == TODAY_DATE && $m == TODAY_MONTH);

		$class = array();
		switch($w){
			case 0:	//日曜日の場合
				$class[] = "sun";
				$da = "sun";
				$this->wc++;
				break;
			case 6:	//土曜日の場合
				$class[] = "sat";
				$da = "sat";
				break;
		}

		if($isToday) {
			$class[] = "today";
			$da = "today";
		}

		if(is_array($this->cd) && count($this->cd) > 0 && in_array(date("Ymd", $this->cd), $this->holidayList)) {
			$class[] = "holiday";
			$da = "holiday";
		}
		if($isOtherMonth) {
			$class[] = "other";
			$da = "other";
		}

		//今日より前の日付
		if($this->isBefore){
			if(($m == TODAY_MONTH && $i < TODAY_DATE) || $isOtherMonth){
				$class[] = "before";
				$da = "before";
			}
		}

		//次の月 週が4週目以降 4週目、5週目の場合は念の為に日付を確認し、6週目以降であればすべての日にnextを付与
		if($this->isNextMonth){
			if(($this->wc == 4 && $i < 7) || ($this->wc == 5 && $i < 15) || $this->wc > 5){
				$class[] = "next";
				$da = "next";
			}
		}

		//定休日
		if(count($rhList) > 0 && in_array($i, $rhList)){
			$class[] = "reg";
			$da = "reg";
		}

		if(count($class)){
			$html[] = "			<td class=\"" . implode(" ", $class) . "\">";
		}else{
			$html[] = "			<td>";
		}

		$html[] = $this->handleFunc($i, $this->cd, $this->wc, $da, $isOtherMonth);
		$html[] = "</td>";

		return implode("", $html);
	}

	/**
	 * override
	 */
	function handleFunc($i, $cd, $wc, $da, $isOtherMonth){
		return $i;
	}

	private function getNextMonthDate($i, $t){
		return mktime(0, 0, 0, date("n", $t) + 1, $i, date("Y", $t));
	}
	private function getPrevMonthDate($i, $t){
		return mktime(0, 0, 0, date("n", $t) - 1, $i, date("Y", $t));
	}

	private function getPrevMonthLastDate($m, $y){
		return mktime(0, 0, 0, $m, 0, $y);
	}

	/** 月や日が1桁の場合は二ケタにする */
	private function convert($s){
		if(strlen($s) === 1) $s = "0" . $s;
		return $s;
	}

	function setRegularHolidays($days){
		$this->regularHolidayList = $days;
	}

	function getDays(){
		return $this->days;
	}
}
