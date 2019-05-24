<?php
/*
 * Created on 2009/07/28
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

class CalendarExpandSeatOrderCustomField extends SOYShopOrderCustomfield{

	function __construct(){
		SOY2::import("module.plugins.calendar_expand_seat.util.ExpandSeatUtil");
	}

	function clear(CartLogic $cart){}

	function doPost($param){

		$cart = $this->getCart();
		ExpandSeatUtil::set($cart, "representative", $param["Representative"]);
		ExpandSeatUtil::set($cart, "companion", $param["Companion"]);
	}

	function complete(CartLogic $cart){}
	function hasError($param){}

	function getForm(CartLogic $cart){

		//出力する内容を格納する
		$array = array();
		//$obj = array("name" => "", "description" => "", "isRequired" => false, "error" => "");

		$rainSizes = array("S", "M", "L");
		$shoesSizes = array("S", "M", "L");

		$items = $cart->getItems();
		if(!count($items)) return array();

		$item = array_shift($items);
		$seat = $item->getItemCount();

		$repValues = ExpandSeatUtil::get($cart, "representative");
		$comValues = ExpandSeatUtil::get($cart, "companion");
		for($i = 0; $i < $seat; $i++){
			$obj = array();
			//{#br#}はDOMの方で改行
			if($i === 0){
				$obj["name"] = "代表者様";
			}else{
				$name = (isset($comValues[$i - 1]["name"])) ? $comValues[$i - 1]["name"] : "";
				$obj["name"] = "同行者様" . $i . "{#br#}" . $name . "様";
			}
			if($i === 0){
				$prop = "customfield_module[Representative]";
			}else{
				$prop = "customfield_module[Companion][" . ($i - 1) . "]";
			}

			$form = array();

			$rainSelected = ($i === 0) ? $repValues["rain"] : null;
			$shoesSelected = ($i === 0) ? $repValues["shoes"] : null;

			if(is_null($rainSelected) && isset($comValues[$i - 1]["rain"])) $rainSelected = $comValues[$i - 1]["rain"];
			if(is_null($shoesSelected) && isset($comValues[$i - 1]["shoes"])) $shoesSelected = $comValues[$i - 1]["shoes"];

			//雨具
			$rainSelect = array();
			$rainSelect[] = "<select name=\"" . $prop . "[rain]\" class=\"form-control\" required=\"required\">";
			$rainSelect[] = "<option></option>";
			foreach($rainSizes as $size){
				if(strlen($rainSelected) && $rainSelected == $size){
					$rainSelect[] = "<option value=\"" . $size . "\" selected>" . $size . "</option>";
				}else{
					$rainSelect[] = "<option value=\"" . $size . "\">" . $size . "</option>";
				}

			}
			$rainSelect[] = "</select>";

			//登山靴
			$shoesSelect = array();
			$shoesSelect[] = "<select name=\"" . $prop . "[shoes]\" class=\"form-control\" required=\"required\">";
			$shoesSelect[] = "<option></option>";
			foreach($shoesSizes as $size){
				if(strlen($shoesSelected) && $shoesSelected == $size){
					$shoesSelect[] = "<option value=\"" . $size . "\" selected>" . $size . "</option>";
				}else{
					$shoesSelect[] = "<option value=\"" . $size . "\">" . $size . "</option>";
				}
			}
			$shoesSelect[] = "</select>";

			$form[] = "<div class=\"form-inline\">";
			$form[] = "雨具サイズ　：" . implode("\n", $rainSelect);
			$form[] = "</div>";
			$form[] = "<div class=\"form-inline mt-1\">";
			$form[] = "登山靴サイズ：" . implode("\n", $shoesSelect);
			$form[] = "</div>";

			$obj["description"] = implode("\n", $form);

			$key = ($i === 0) ? "representative" : "companion" . ($i - 1);
			$array[$key] = $obj;
		}

		//選択した内容はsoyshop.user.customfieldの方で出力や登録
		return $array;
	}

	function display($orderId){}

	/**
	 * @param int $orderID
	 * @return array labelとformの連想配列を格納
	 */
	function edit($orderId){}

	/**
	 * 編集画面で編集するための設定内容を取得する
	 * @param int $orderId
	 * @return array saveするための配列
	 */
	function config($orderId){}
}
SOYShopPlugin::extension("soyshop.order.customfield", "calendar_expand_seat", "CalendarExpandSeatOrderCustomField");
