<?php

class CommonOrderConfirmCheckCommon{
	
	public static function getConfig(){
		return SOYShop_DataSets::get("common_order_confirm_check", array(
			"text" => "入力内容に間違いはございませんか？",
			"error" => "チェックされていません。"
		));
	}
	
}
?>