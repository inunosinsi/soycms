<?php
/*
 */
class AutoCompletionItemName extends SOYShopItemNameBase{

	function getForm(SOYShop_Item $item){
		$readings = SOY2Logic::createInstance("module.plugins.auto_completion_item_name.logic.AutoCompleteDictionaryLogic")->getReadingsByItemId($item->getId());

		$html = array();

		//読み方
		SOY2::import("module.plugins.auto_completion_item_name.util.AutoCompletionUtil");
		foreach(AutoCompletionUtil::getItemTypes() as $t => $label){
			$html[] = "<div class=\"form-group\">";
			$html[] = "<label>読み方(" . $label . ")</label>";
			$html[] = "<input type=\"text\" name=\"AutoCompletion[" . $t . "]\" class=\"form-control\" value=\"" . $readings[$t] . "\">";
			$html[] = "</div>";
		}

		return implode("\n", $html);
	}

	function doPost(SOYShop_Item $item){
		if(isset($_POST["AutoCompletion"])){
			SOY2::import("module.plugins.auto_completion_item_name.util.AutoCompletionUtil");
			$arr = array();
			foreach(AutoCompletionUtil::getItemTypes() as $t => $label){
				$arr[$t] = (isset($_POST["AutoCompletion"][$t])) ? $_POST["AutoCompletion"][$t] : "";
			}
			
			SOY2Logic::createInstance("module.plugins.auto_completion_item_name.logic.AutoCompleteDictionaryLogic")->save($item->getId(), $arr);
		}
	}
}
SOYShopPlugin::extension("soyshop.item.name", "auto_completion_item_name", "AutoCompletionItemName");
