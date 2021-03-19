<?php

class ItemCheckListLogic extends SOY2LogicBase {

	function __construct(){
		SOY2::import("domain.cms.DataSets");
	}

	function save($item, $type = "entry"){
		DataSets::put($type . "_csv_item_check", $item);
	}

	function buildJSCode($type = "item"){

		$scripts = array();
		$scripts[] = "<script>";

		//一つずつチェックを入れていく
		$checks = DataSets::get($type . "_csv_item_check", array());
		if(count($checks)){
			//すべてのチェックを外す
			$scripts[] = "$('input[name^=\"item[\"]').prop('checked', false);";

			//ダミーにチェックを入れる
			$scripts[] = "$('input[name$=\"_dummy\"]').prop('checked', true);";

			foreach($checks as $key => $val){
				if(is_string($val) && $val != 1 && $val != "checked") continue;

				//多言語プラグインの対応
				if(is_array($val) && count($val)){
					foreach($val as $k => $v){
						if(is_string($v) && $v != 1 && $val != "checked") continue;
						$scripts[] = "if($('input[name=\"item[" . $key . "][" . $k . "]\"]')) $('input[name=\"item[" . $key . "][" . $k . "]\"]').prop('checked', true);";

						//カスタムサーチフィールドがあった場合はもう一つチェックを追加。インポート用
						if(strpos($key, "custom_search_field") !== false){
							$scripts[] = "if($('input[name=\"item[" . $key . "(" . $k . ")]\"]')) $('input[name=\"item[" . $key . "(" . $k . ")]\"]').prop('checked', true);";
						}
					}
				}else{
					$scripts[] = "if($('input[name=\"item[" . $key . "]\"]')) $('input[name=\"item[" . $key . "]\"]').prop('checked', true);";
				}
			}
		}

		$scripts[] = "";
		$scripts[] = "function toggleItemCheck(ele){";
		$scripts[] = "	if($(ele).prop('checked')){";
		//すべてのチェックを付ける
		$scripts[] = "		$('input[name^=\"item[\"]').prop('checked', true);";
		$scripts[] = "	} else {";
		//すべてのチェックを外す
		$scripts[] = "		$('input[name^=\"item[\"]').prop('checked', false);";
		//ダミーにチェックを入れる
		$scripts[] = "		$('input[name$=\"_dummy\"]').prop('checked', true);";
		$scripts[] = "	}";
		$scripts[] = "}";

		$scripts[] = "</script>";

		return implode("\n", $scripts);
	}
}
