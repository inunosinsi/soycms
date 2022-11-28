<?php

class CalendarBaseComponent extends SOY2LogicBase{

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

	//今月のカレンダーであるか？
	private $isThisMonth;

	function __construct(){}

	function build(int $y, int $m, bool $dspOtherMD=true, bool $dspCaption=true, bool $dspRegHol=false){

		//週のカウントを初期化する
		$this->wc = 0;

		//本日の日付を取得
		if(!defined("TODAY_DATE")) define("TODAY_DATE", date("j", time()));

		//他の月を表示しない場合かつ日曜スタートでない場合は1を加算しておく
		if(!$dspOtherMD && date("w", mktime(0, 0, 0, $m, 1, $y)) != "0") $this->wc++;

		//翌月の日付を表示するか？
		$this->dspOtherMonthDate = $dspOtherMD;
		$this->isThisMonth = true;

		//captionを表示するか
		$this->dspCaption = $dspCaption;

		//祭日を調べる
		self::useGoogleCalendarDataAPI($y, $m);

		//定休日を調べる
		if($dspRegHol) self::getRegularHolidayList();
		return self::create(mktime(0, 0, 0, $m, 1, $y));
	}

	private function useGoogleCalendarDataAPI($y, $m){
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
	}

	//定休日を取得する
	private function getRegularHolidayList(){}

	private function create($t){
		//その月の日付の数
		$last = date("t", $t);

		//表示用の年月日を取得
		$y = date("Y", $t);
		$m = date("n", $t);
		$d = date("j", $t);

		//カレンダを表示する
		$html = array();
		$html[] = "<table>";
		if($this->dspCaption){
			$html[] = "	<caption>" . $y . "年" . $m . "月</caption>";
		}
		$html[] = "	<thead>";
		$html[] = "		<tr>";
		$html[] = "			<th class=\"sun\">日</th>\n<th>月</th>\n<th>火</th>\n<th>水</th>\n<th>木</th>\n<th>金</th>\n<th class=\"sat\">土</th>";
		$html[] = "		</tr>";
		$html[] = "	</thead>";

		$html[] = "	<tbody>";

		//<tr>から</tr>までの<td>のカウンター
		$counter = 0;
		//カレンダの日付を作成する
		for($i = 1; $i <= $last; $i++){
			//カラムごとの曜日を取得する
			$this->cd = mktime(0, 0, 0, $m, $i, $y);
			$w = date("w", $this->cd);

			//その月の初日
			if($i === 1){
				$html[] = "		<tr>";

				//初日まで（前月）
				for($j = 1; $j <= $w; $j++){
					if($this->dspOtherMonthDate){
						$lastDate = date("j", self::getPrevMonthLastDate($m, $y));
						$int = $lastDate - $w + $j;
						$html[] = self::createDayColumn($int, 0, 0, 0, true, $t);
					}else{
						$html[] = "			<td>&nbsp;</td>";
					}

					$counter++;
				}

				$html[] = self::createDayColumn($i, $m, $w, $d, $last);
				$counter++;
				//土曜日の場合は</tr>で閉じる
				if($w == 6){
					$html[] = "		</tr>";
				}
			//二日目以降
			}else{
				if($w == 0){
					$html[] = "		<tr>";
				}
				$html[] = self::createDayColumn($i, $m, $w, $d, $last);
				$counter++;

				//末日以降（次の月）
				if($i == $last && $w < 6){
					for($k = 1; $k <= 7 - $counter; $k++){
						if($this->dspOtherMonthDate){
							$html[] = self::createDayColumn($k, $m, 0, 0, 0, true, $t);
						}else{
							$html[] = "			<td>&nbsp;</td>";
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

		$isToday = ($this->isThisMonth && $i == TODAY_DATE);

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

		if(count($this->cd) > 0 && in_array(date("Ymd", $this->cd), $this->holidayList)) {
			$class[] = "holiday";
			$da = "holiday";
		}
		if($isOtherMonth) {
			$class[] = "other";
			$da = "other";
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
	function handleFunc(int $i, int $cd, int $wc, string $da, bool $isOtherMonth){
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
}
