<?php

class AnalyticsPluginUtil{
	
	const MODE_MONTH = "month";
	
	const TYPE_MONTH 		= 	"月次売上集計";
	const TYPE_AREA 		= 	"都道府県別購入者数";
	const TYPE_ORDERCOUNT 	= 	"顧客毎の注文回数";
	const TYPE_NEWCUSTOMER 	= 	"月次新規注文集計";
	const TYPE_REPEAT 		= 	"購入者毎の注文回数分布";
	const TYPE_REPEATMONTH 	= 	"月次リピート率集計";
	const TYPE_ITEMRATE 	= 	"商品毎の注文個数分布";
	const TYPE_LANGUAGE 	= 	"言語毎の注文回数";
	const TYPE_CARRIER 		= 	"キャリア毎の注文回数";
	
	/**
	 * タイトルを取得する
	 */
	public static function getTitle(){
		
		$mode = (isset($_POST["AnalyticsPlugin"]["type"])) ? $_POST["AnalyticsPlugin"]["type"] : "month";
		switch($mode){
			case "area":
				$title = self::TYPE_AREA;
				break;
			case "repeat":
				$title = self::TYPE_REPEAT;
				break;
			case "ordercount":
				$title = self::TYPE_ORDERCOUNT;
				break;
			case "newcustomer":
				$title = self::TYPE_NEWCUSTOMER;
				break;
			case "repeatmonth":
				$title = self::TYPE_REPEATMONTH;
				break;
			case "itemrate":
				$title = self::TYPE_ITEMRATE;
				break;
			case "language":
				$title = self::TYPE_LANGUAGE;
				break;
			case "carrier":
				$title = self::TYPE_CARRIER;
				break;
			default:
				$title = self::TYPE_MONTH;
		}
		
		return $title;
	}
	
	/**
	 * カレンダーに入れた値からタイムスタンプを取得する
	 * @param string mode, boolean 開始日の制限
	 * @return timestamp
	 */
	public static function convertTitmeStamp($mode = "start", $limitation = true){
		
		//値を入力した場合
		if(isset($_POST["AnalyticsPlugin"]["period"][$mode]) && strlen($_POST["AnalyticsPlugin"]["period"][$mode]) > 0){
			$values = explode("-", $_POST["AnalyticsPlugin"]["period"][$mode]);
			$timestamp = mktime(0, 0, 0, $values[1], $values[2], $values[0]);
			
			if($mode == "start"){
				//終わりの値よりも大きい数字の場合は終りの日の一年前にする
				if(isset($_POST["AnalyticsPlugin"]["period"]["end"]) && strlen($_POST["AnalyticsPlugin"]["period"]["end"]) > 0){
					$endValues = explode("-", $_POST["AnalyticsPlugin"]["period"]["end"]);
					if($timestamp > mktime(0, 0, 0, $endValues[1], $endValues[2], $endValues[0])){
						$timestamp = mktime(0, 0, 0, $endValues[1], $endValues[2], $endValues[0] - 1);
					}
				}
				
				//今日よりも後の場合は去年の今日にする
				if($timestamp > time()){
					$timestamp = mktime(0, 0, 0, date("n", time()), date("j", time()), date("Y", time()) - 1);
				}
			}else{
				$timestamp += 24 * 60 * 60;
			}
			
		//値が入力されていない場合
		}else{
			
			if($mode == "start"){
				//終わりの方に入力がある場合は指定した
				if(isset($_POST["AnalyticsPlugin"]["period"]["end"]) && strlen($_POST["AnalyticsPlugin"]["period"]["end"])){
					$values = explode("-", $_POST["AnalyticsPlugin"]["period"]["end"]);
					$timestamp = mktime(0, 0, 0, 1, 1, $values[0]);
				}else{
					if($limitation){
						//一年分のデータを出力する
						$timestamp = mktime(0, 0, 0, date("n", time()) + 1, date("j", time()), date("Y", time()) - 1);
					}else{
						//開始日を指定しない
						$timestamp = 0;
					}
				}
			}else{
				$timestamp = mktime(0, 0, 0, date("m", time()), date("d", time()), date("Y", time())) + 24 * 60 * 60;
			}
		}
		
		return $timestamp;
	}
	
	/**
	 * 横軸用のテキストを取得する
	 * @param timestamp start end
	 * @return string
	 */
	public static function buildDateRange($start, $end){
		$startYear = date("Y", $start);
		$endYear = date("Y", $end);
		
		$yearDiff = $endYear - $startYear;
		
		$ranges = array();
		
		//同じ年の場合
		if($yearDiff === 0){
			$startMonth = date("n", $start);
			$endMonth = date("n", $end);
			
			$counter = 0;
			for($i = $startMonth; $i <= $endMonth; $i++){
				if($counter === 0){
					$ranges[] = "\"" . $startYear . "年" . $i . "月\"";
				}else{
					$ranges[] = "\"" . $i . "月\"";
				}
				$counter++;
			}
		//違う場合
		}else{
			$yearCounter = 0;
			
			for($i = $yearCounter; $i <= $yearDiff; $i++){
				//最初の年
				if($i === 0){
					$startYearMonth = date("n", $start);
					
					$counter = 0;
					for($j = $startYearMonth; $j <= 12; $j++){
						if($counter === 0){
							$ranges[] = "\"" . $startYear . "年" . $j . "月\"";
						}else{
							$ranges[] = "\"" . $j . "月\"";
						}
						$counter++;
					}
					
				//間の年
				}else if($i > 0 && $i < $yearDiff){
					for($j = 1; $j <= 12; $j++){
						if($j === 1){
							$thisYear = $startYear + $i;
							$ranges[] = "\"" . $thisYear . "年" . $j . "月\"";
						}else{
							$ranges[] = "\"" . $j . "月\"";
						}
					}
										
				//最後の年の処理
				}else if($i === $yearDiff){
					
					$endYearMonth = date("n", $end);
					
					for($j = 1; $j <= $endYearMonth; $j++){
						if($j === 1){
							$thisYear = $startYear + $i;
							$ranges[] = "\"" . $thisYear . "年" . $j . "月\"";
						}else{
							$ranges[] = "\"" . $j . "月\"";
						}
					}								
				}
			}
		}
		
		return implode(",", $ranges);
	}
}
?>