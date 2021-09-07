<?php
/*
 */
class AutoCompletionItemName extends SOYShopItemNameBase{

	function getForm(SOYShop_Item $item){
		SOY2::import("module.plugins.auto_completion_item_name.util.AutoCompletionUtil");
		$values = (is_numeric($item->getId())) ? AutoCompletionUtil::getReadings($item->getId()) : array("hiragana" => "", "katakana" => "");

		$html = array();

		//読み方
		foreach(array("hiragana", "katakana") as $t){
			$label = "読み方(";
			$label .= ($t == "hiragana") ? "ひらがな" : "カタカナ";
			$label .= ")";

			$html[] = "<div class=\"form-group\">";
			$html[] = "<label>" . $label . "</label>";
			$html[] = "<input type=\"text\" name=\"AutoCompletion[" . $t . "]\" class=\"form-control\" value=\"" . $values[$t] . "\">";
			$html[] = "</div>";
		}

		return implode("\n", $html);
	}

	function doPost(SOYShop_Item $item){
		if(isset($_POST["AutoCompletion"])){
			$hiragana = (isset($_POST["AutoCompletion"]["hiragana"])) ? $_POST["AutoCompletion"]["hiragana"] : "";
			$katakana = (isset($_POST["AutoCompletion"]["katakana"])) ? $_POST["AutoCompletion"]["katakana"] : "";
			SOY2::import("module.plugins.auto_completion_item_name.util.AutoCompletionUtil");
			AutoCompletionUtil::save($item->getId(), $hiragana, $katakana);
		}
	}
}
SOYShopPlugin::extension("soyshop.item.name", "auto_completion_item_name", "AutoCompletionItemName");
