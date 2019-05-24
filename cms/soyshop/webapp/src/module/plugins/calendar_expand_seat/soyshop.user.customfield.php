<?php
/*
 * Created on 2009/07/28
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

class CalendarExpandSeatUserCustomField extends SOYShopUserCustomfield{

	function __construct(){
		SOY2::import("module.plugins.calendar_expand_seat.util.ExpandSeatUtil");
	}

	function clear($app){
		//$app->clearAttribute($attributeKey);
	}

	/**
	 * @param array $param 中身は$_POST["user_customfield"]
	 */
	function doPost($param){
		if(defined("SOYSHOP_CART_MODE") && SOYSHOP_CART_MODE){
			//@ToDo Utilでgetter setterを用意する
			$app = $this->getApp();
			ExpandSeatUtil::set($app, "representative", $param["Representative"]);
			ExpandSeatUtil::set($app, "companion", $param["Companion"]);
		}
	}

	/**
	 * マイページ・カートの登録で表示するフォーム部品の生成
	 * @param MyPageLogic || CartLogic $app
	 * @param integer $userId
	 * @return array(["name"], ["description"], ["error"])
	 *
	 */
	function getForm($app, $userId){
		//カートの時のみ
		if(defined("SOYSHOP_CART_MODE") && SOYSHOP_CART_MODE){
			$array = array();

			//人数分だけフォームを出力　最初の一人は代表者としてご旅行時緊急連絡先者氏名、続柄と緊急連絡先者電話番号のフォームを出力
			$items = $app->getItems();
			$item = array_shift($items);
			$seat = $item->getItemCount();

			//代表者の値
			$values = ExpandSeatUtil::get($app, "representative");

			$obj = array();
			$obj["name"] = "ご旅行時緊急連絡先者氏名";
			$obj["form"] = "<input type=\"text\" name=\"user_customfield[Representative][emergency_name]\" class=\"form-control\" required=\"required\" value=\"" . self::escape($values, "emergency_name") . "\">";
			$array["emergency_name"] = $obj;

			$obj["name"] = "続柄";
			$obj["form"] = "<input type=\"text\" name=\"user_customfield[Representative][relationship]\" class=\"form-control\" required=\"required\" value=\"" . self::escape($values, "relationship") . "\">";
			$array["relationship"] = $obj;

			$obj["name"] = "緊急連絡先者電話番号";
			$obj["form"] = "<input type=\"text\" name=\"user_customfield[Representative][emergency_tel]\" class=\"form-control\" required=\"required\" value=\"" . self::escape($values, "emergency_tel") . "\" style=\"ime-mode:inactive;\">";
			$array["emergency_tel"] = $obj;

			//同行者
			if(($seat - 1) > 0){
				$vv = ExpandSeatUtil::get($app, "companion");
				for($i = 0; $i < $seat - 1; $i++){
					$values = (isset($vv[$i])) ? $vv[$i] : array();

					$obj = array();
					$obj["name"] = "同行者" . ($i + 1);
					$forms = array();
					$forms[] = "<div class=\"form-inline\">";
    				$forms[] = "氏名　　　：<input type=\"text\" name=\"user_customfield[Companion][" . $i . "][name]\" class=\"form-control\" required=\"required\" value=\"" . self::escape($values, "name") . "\">";
					$forms[] = "</div>";
					$forms[] = "<div class=\"form-inline mt-2 mb-2\">";
					$sex = (int)self::escape($values, "sex");
					if(!strlen($sex)) $sex = 0;
					$f = "性別　　　：";
					if($sex == 0){
						$f .= "<label><input type=\"radio\" name=\"user_customfield[Companion][" . $i . "][sex]\" value=\"0\" checked=\"checked\"> 男性</label>&nbsp;&nbsp;";
						$f .= "<label><input type=\"radio\" name=\"user_customfield[Companion][" . $i . "][sex]\" value=\"1\"> 女性</label>";
					}else{
						$f .= "<label><input type=\"radio\" name=\"user_customfield[Companion][" . $i . "][sex]\" value=\"0\"> 男性</label>";
						$f .= "<label><input type=\"radio\" name=\"user_customfield[Companion][" . $i . "][sex]\" value=\"1\" checked=\"checked\"> 女性</label>";
					}
    				$forms[] = $f;
					$forms[] = "</div>";
					$forms[] = "<div class=\"form-inline\">";
    				$forms[] = "出発時年齢：<input type=\"number\" name=\"user_customfield[Companion][" . $i . "][age]\" class=\"form-control\" required=\"required\" value=\"" . self::escape($values, "age") . "\">";
					$forms[] = "</div>";
					$obj["form"] = implode("\n", $forms);
					$array["companion" . $i] = $obj;
				}
			}

			return $array;
		}
	}

	/**
	 * 各項目ごとに、createAdd()を行う。 soy:id="usf_{field_id}"にする
	 * @param MyPageLogic || CartLogic $app
	 * @param SOYBodyComponentBase $pageObj
	 * @param integer $userId
	 */
	function buildNamedForm($app, SOYBodyComponentBase $pageObj, $userId=null){}

	function hasError($param){
		/** @ToDo 必須の設定をそのうち追加したいところ **/
	}

	/**
	 * @param MyPageLogic || CartLogic $app
	 */
	function confirm($app){
		//カートの時のみ
		if(defined("SOYSHOP_CART_MODE") && SOYSHOP_CART_MODE){
			//出力する内容を格納する
			$array = array();
			//$obj = array("name" => "", "confirm" => "");

			$values = ExpandSeatUtil::get($app, "representative");

			//代表者の情報
			$array["emergency_name"] = array("name" => "ご旅行時緊急連絡先者氏名", "confirm" => self::escape($values, "emergency_name"));
			$array["relationship"] = array("name" => "続柄", "confirm" => self::escape($values, "relationship"));
			$array["emergency_tel"] = array("name" => "緊急連絡先者電話番号", "confirm" => self::escape($values, "emergency_tel"));

			$c = "雨具サイズ　　：" . self::escape($values, "rain") . "<br>";
			$c .= "登山靴サイズ　：" . self::escape($values, "shoes");
			$array["representative"] = array("name" => "代表者様", "confirm" => $c);

			//同行者
			$vv = ExpandSeatUtil::get($app, "companion");
			foreach($vv as $i => $v){
				if(!is_numeric($i)) continue;
				$c = "氏名　　　　　：" . self::escape($v, "name") . "<br>";
				$sex = (int)self::escape($v, "sex");
				$sexLabel = (!strlen($sex) || $sex === 0) ? "男性" : "女性";
				$c .= "性別　　　　　：" . $sexLabel . "<br>";
				$c .= "出発時年齢　　：" . self::escape($v, "age") . "歳<br>";
				$c .= "雨具サイズ　　：" . self::escape($v, "rain") . "<br>";
				$c .= "登山靴サイズ　：" . self::escape($v, "shoes");

				$array["companion" . $i] = array("name" => "同行者" . ($i + 1), "confirm" => $c);
			}

			return $array;
		}
	}

	/**
	 * @param MyPageLogic || CartLogic $app
	 * @param integer $userId
	 */
	function register($app, $userId){
		//カートの時のみ
		if(defined("SOYSHOP_CART_MODE") && SOYSHOP_CART_MODE){
			$v = $app->getAttribute("representative");
			$values = (isset($v) && strlen($v)) ? soy2_unserialize($v) : array();

			$app->setOrderAttribute("emergency_name", "ご旅行時緊急連絡先者氏名", self::escape($values, "emergency_name"));
			$app->setOrderAttribute("relationship", "続柄", self::escape($values, "relationship"));
			$app->setOrderAttribute("emergency_tel", "緊急連絡先者電話番号", self::escape($values, "emergency_tel"));

			$c = "雨具サイズ　：" . self::escape($values, "rain") . "\n";
			$c .= "登山靴サイズ：" . self::escape($values, "shoes");
			$app->setOrderAttribute("representative", "代表者", $c);

			//同行者
			$v = $app->getAttribute("companion");
			$vv = (isset($v) && strlen($v)) ? soy2_unserialize($v) : array();
			foreach($vv as $i => $v){
				if(!is_numeric($i)) continue;
				$c = "氏名　　　　：" . self::escape($v, "name") . "\n";
				$sex = (int)self::escape($v, "sex");
				$sexLabel = (!strlen($sex) || $sex === 0) ? "男性" : "女性";
				$c .= "性別　　　　：" . $sexLabel . "\n";
				$c .= "出発時年齢　：" . self::escape($v, "age") . "歳\n";
				$c .= "雨具サイズ　：" . self::escape($v, "rain") . "\n";
				$c .= "登山靴サイズ：" . self::escape($v, "shoes");

				$app->setOrderAttribute("companion" . $i, "同行者" . ($i + 1), $c);
			}
		}
	}

	private function escape($values, $key){
		return (isset($values[$key])) ? htmlspecialchars($values[$key], ENT_QUOTES, "UTF-8") : "";
	}
}
SOYShopPlugin::extension("soyshop.user.customfield", "calendar_expand_seat", "CalendarExpandSeatUserCustomField");
