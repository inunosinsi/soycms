<?php
/*
 */
class ItemSubtitleItemName extends SOYShopItemNameBase{

	private $itemAttributeDao;

	function getForm(SOYShop_Item $item){
		$html = array();
		$html[] = "<div class=\"form-group\">";
		$html[] = "<label>サブタイトル</label>";
		$html[] = "<input type=\"text\" name=\"Item[subtitle]\" class=\"form-control\" style=\"width:100%;\" value=\"" . $item->getSubtitle() . "\">";
		// foreach(UtilMultiLanguageUtil::allowLanguages() as $lang => $title){
		// 	if($lang == UtilMultiLanguageUtil::LANGUAGE_JP) continue;
		// 	$itemName = self::getItemAttribute($item->getId(), $lang)->getValue();
		//
		// 	$html[] = "<dt>商品名(" . $lang . ")&nbsp;<a href=\"" . SOY2PageController::createLink("Config.Detail?plugin=util_multi_language&item_id=" . $item->getId() . "&language=" . $lang) ."\">カスタムフィールドの設定</a></dt>";
		// 	$html[] = "<dd>";
		// 	$html[] = "<input name=\"LanguageConfig[item_name_" . $lang . "]\" value=\"" . $itemName . "\" type=\"text\" class=\"form-control\">";
		// 	$html[] = "</dd>";
		// }

		return implode("\n", $html);
	}

	function doPost(SOYShop_Item $item){}
}
SOYShopPlugin::extension("soyshop.item.name", "common_item_subtitle", "ItemSubtitleItemName");
