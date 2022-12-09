<?php

class DateSelectBoxComponent {

	public static function build(int $n, string $name, string $typ="year", int $startYear=0, int $endYear=1){
		//fieldIDをnameから取得する。関数の引数を追加したくないため
		preg_match('/Customfield\[(.*?)\]/', $name, $tmp);
		$fieldId = (isset($tmp[1])) ? $tmp[1] : "";

		$html[] = "<select name=\"" . $name . "[" . $typ . "]\" id=\"" . $fieldId . "_" . $typ . "\">";
		$html[] = "<option></option>";
		switch($typ){
			case "year":
				$start = ($startYear > 0) ? $startYear : date("Y", SOY2DAOFactory::create("order.SOYShop_OrderDAO")->getFirstOrderDate()) - 1;
				$end = date("Y", time()) + $endYear;
				for($i = $start; $i <= $end; $i++){
					$html[] = self::_addOpt($i, $n);
				}
				break;
			case "month":
				for($i = 1; $i <= 12; $i++){
					//if(strlen($i) === 1) $i = "0" . $i; ← 必要？
					$html[] = self::_addOpt($i, $n);
				}
				break;
			case "day":
				for($i = 1; $i <= 31; $i++){
					//if(strlen($i) === 1) $i = "0" . $i; ← 必要？
					$html[] = self::_addOpt($i, $n);
				}
				break;
		}

		$html[] = "</select>";

		return implode("\n", $html);
	}

	private static function _addOpt(int $i, int $n){
		if($i == $n){
			return "<option value=\"" . $i . "\" selected=\"selected\">" . $i . "</option>";
		}else{
			return "<option value=\"" . $i . "\">" . $i . "</option>";
		}
	}
}
