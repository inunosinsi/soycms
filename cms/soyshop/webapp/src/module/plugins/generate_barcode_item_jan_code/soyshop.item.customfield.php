<?php
/*
 */
SOY2::import("module.plugins.generate_barcode_item_jan_code.util.GenerateJancodeUtil");
class GenerateBarcodeItemJanCodeItemCustomField extends SOYShopItemCustomFieldBase{

	function doPost(SOYShop_Item $item){
		if(isset($_POST["jancode"]) && strlen($_POST["jancode"])){
			GenerateJancodeUtil::saveJancode($_POST["jancode"], $item->getId());
		}
	}

	function getForm(SOYShop_Item $item){
		/** @ToDo チェックデジット付きであれば12桁で良いかも **/
		$jancode = GenerateJancodeUtil::getJancode($item->getId());

		$html = array();
		$html[] = "<div class=\"form-group\">";
		$html[] = "<label>JANコード(13桁)</label>";
		$html[] = "<div class=\"form-inline\">";

		$jancodeJpg = GenerateJancodeUtil::getJancodeImagePath($jancode . ".jpg", $item->getCode());
		if(strlen($jancodeJpg)){
			$html[] = "<img src=\"" . $jancodeJpg . "\">";
			$html[] = "&nbsp;<a href=\"" . $jancodeJpg . "\" download=\"" . $jancode . ".jpg\" class=\"btn btn-default\">ダウンロード</a>";
			$html[] = "<br>";
		}

		$html[] = "<input type=\"number\" name=\"jancode\" value=\"" . $jancode . "\" style=\"width:30%;\" placeholder=\"4900000000000\" pattern=\"\d{13}\">";

		$html[] = "</div>";
		$html[] = "</div>";
		return implode("\n", $html);
	}

	function onOutput($htmlObj, SOYShop_Item $item){}
	function onDelete($id){}
}

SOYShopPlugin::extension("soyshop.item.customfield", "generate_barcode_item_jan_code", "GenerateBarcodeItemJanCodeItemCustomField");
