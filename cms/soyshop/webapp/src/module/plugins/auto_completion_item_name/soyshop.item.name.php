<?php
/*
 */
class AutoCompletionItemName extends SOYShopItemNameBase{

	function getForm(SOYShop_Item $item){
		$readings = SOY2Logic::createInstance("module.plugins.auto_completion_item_name.logic.AutoCompleteDictionaryLogic")->getReadingsByItemId($item->getId());

		$html = array();

		//読み方
		foreach(array("hiragana", "katakana") as $t){
			$label = "読み方(";
			$label .= ($t == "hiragana") ? "ひらがな" : "カタカナ";
			$label .= ")";

			$html[] = "<div class=\"form-group\">";
			$html[] = "<label>" . $label . "</label>";
			$html[] = "<input type=\"text\" name=\"AutoCompletion[" . $t . "]\" class=\"form-control\" value=\"" . $readings[$t] . "\">";
			$html[] = "</div>";
		}

		return implode("\n", $html);
	}

	function doPost(SOYShop_Item $item){
		if(isset($_POST["AutoCompletion"])){
			$hiragana = (isset($_POST["AutoCompletion"]["hiragana"])) ? $_POST["AutoCompletion"]["hiragana"] : "";
			$katakana = (isset($_POST["AutoCompletion"]["katakana"])) ? $_POST["AutoCompletion"]["katakana"] : "";
			SOY2Logic::createInstance("module.plugins.auto_completion_item_name.logic.AutoCompleteDictionaryLogic")->save($item->getId(), $hiragana, $katakana);
		}
	}
}
SOYShopPlugin::extension("soyshop.item.name", "auto_completion_item_name", "AutoCompletionItemName");
