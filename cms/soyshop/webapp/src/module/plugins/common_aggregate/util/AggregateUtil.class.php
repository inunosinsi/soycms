<?php

class AggregateUtil{
	
	const MODE_MONTH = "month";
	const MODE_DAY = "day";
	const MODE_ITEMRATE = "itemrate";
	const MODE_AGE = "age";
	
	const TYPE_MONTH 		= 	"月次売上集計";
	const TYPE_DAY 			= 	"日次売上集計";
	const TYPE_ITEMRATE 	= 	"商品毎の売上集計";
	const TYPE_AGE			= 	"年齢別売上集計";
	
	/**
	 * タイトルを取得する
	 */
	public static function getTitle(){
		
		$mode = (isset($_POST["Aggregate"]["type"])) ? $_POST["Aggregate"]["type"] : "month";
		switch($mode){
			case self::MODE_ITEMRATE:
				$title = self::TYPE_ITEMRATE;
				break;
			case self::MODE_DAY:
				$title = self::TYPE_DAY;
				break;
			case self::MODE_AGE:
				$title = self::TYPE_AGE;
				break;
			case self::MODE_MONTH:
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
		if(isset($_POST["Aggregate"]["period"][$mode]) && strlen($_POST["Aggregate"]["period"][$mode]) > 0){
			$values = explode("-", $_POST["Aggregate"]["period"][$mode]);
			$timestamp = mktime(0, 0, 0, $values[1], $values[2], $values[0]);
			
			if($mode == "start"){
				//終わりの値よりも大きい数字の場合は終りの日の一年前にする
				if(isset($_POST["Aggregate"]["period"]["end"]) && strlen($_POST["Aggregate"]["period"]["end"]) > 0){
					$endValues = explode("-", $_POST["Aggregate"]["period"]["end"]);
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
				if(isset($_POST["Aggregate"]["period"]["end"]) && strlen($_POST["Aggregate"]["period"]["end"])){
					$values = explode("-", $_POST["Aggregate"]["period"]["end"]);
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
}
?>