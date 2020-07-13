<?php

class AggregateUtil{

	const MODE_MONTH 	= "month";
	const MODE_DAY 		= "day";
	const MODE_ITEMRATE = "itemrate";
	const MODE_AGE 		= "age";
	const MODE_CUSTOMER 	= "customer";
	const MODE_ORDER_DATE_CUSTOMFIELD 	= "order_date_customfield"; //隠しモード

	/**
	 * 隠しモードの使い方
	 * name属性でAggregateHiddenValue[label]、AggregateHiddenValue[date_field_id]とAggregateHiddenValue[first_column]を渡す
	 * オーダーカスタムフィールドの値も使用したい場合はAggregateHiddenValue[field_id]とAggregateHiddenValue[field_value]で使用可
	 */

	const TYPE_MONTH 		= 	"月次売上集計";
	const TYPE_DAY 			= 	"日次売上集計";
	const TYPE_ITEMRATE 	= 	"商品毎の売上集計";
	const TYPE_AGE			= 	"年齢別売上集計";
	const TYPE_CUSTOMER 	= "顧客ごと売上集計";

	const METHOD_MODE_TAX 			= "tax";
	const METHOD_MODE_COMMISSION 	= "commission";
	const METHOD_MODE_POINT 		= "point";
	const METHOD_MODE_DISCOUNT 		= "discount";

	const METHOD_INCLUDE_TAX 		= "消費税込み";
	const METHOD_INCLUDE_COMMISSION = "手数料込み";
	const METHOD_INCLUDE_POINT 		= "ポイント値引き込み";
	const METHOD_INCLUDE_DISCOUNT 	= "クーポン値引き等込み";

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
			case self::MODE_CUSTOMER:
				$title = self::TYPE_CUSTOMER;
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
	public static function convertTitmeStamp($mode = "start", $limitation = true, $isCustom=false){

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

				//今日よりも後の場合は去年の今日にする isCustomがtrueの場合は今日よりも後の日を指定していてもそのまま実行
				if(!$isCustom && $timestamp > time()){
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

	/**
	 * セレクトボックス型のカレンダーから日付のタイムスタンプを取得する
	 */
	public static function getDatePeriodBySelectBox(){

		if(!isset($_POST["Customer"])) return array(mktime(0,0,0,1,1,date("Y")), mktime(0,0,0,12,31,date("Y"))+24*60*60);

		$start_y = (int)$_POST["Customer"]["start"]["year"];
		if(isset($_POST["Customer"]["end"]["year"]) && is_numeric($_POST["Customer"]["end"]["year"]) && $_POST["Customer"]["end"]["year"] > $start_y){
			$end_y = (int)$_POST["Customer"]["end"]["year"];
		}else{
			$end_y = $start_y;
		}

		//月の選択を行っていない時はここで処理を止める
		if(!isset($_POST["Customer"]["start"]["month"]) || !strlen($_POST["Customer"]["start"]["month"])){
			$start = mktime(0,0,0,1,1,$start_y);
			$end = mktime(0,0,0,1,1,$end_y+1) - 1;
			return array($start, $end);
		}

		$start_m = (int)$_POST["Customer"]["start"]["month"];

		/** 諸々の条件を書ける様にはじめはnullにしておく **/
		$end_m = null;
		if(isset($_POST["Customer"]["end"]["month"]) && is_numeric($_POST["Customer"]["end"]["month"])){
			$end_m = (int)$_POST["Customer"]["end"]["month"];
		}

		if(is_null($end_m)) $end_m = $start_m;

		//日の選択を行っていない時はここで処理を止める
		if(!isset($_POST["Customer"]["start"]["day"]) || !strlen($_POST["Customer"]["start"]["day"])){
			$start = mktime(0,0,0,$start_m,1,$start_y);
			if($end_m === 12){
				$end = mktime(0,0,0,1,1,$end_y+1) - 1;
			}else{
				$end = mktime(0,0,0,$end_m+1,1,$end_y) - 1;
			}

			return array($start, $end);
		}

		$start_d = (int)$_POST["Customer"]["start"]["day"];
		$end_d = null;
		if(isset($_POST["Customer"]["end"]["day"]) && is_numeric($_POST["Customer"]["end"]["day"])){
			$end_d = (int)$_POST["Customer"]["end"]["day"];
		}

		/** 諸々の条件を書ける様にはじめはnullにしておく **/
		if(is_null($end_d)) $end_d = $start_d;

		$start = mktime(0,0,0,$start_m,$start_d,$start_y);
		$end = mktime(0,0,0,$end_m,$end_d+1,$end_y)-1;

		return array($start, $end);
	}
}
?>
