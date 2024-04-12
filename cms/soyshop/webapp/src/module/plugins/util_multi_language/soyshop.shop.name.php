<?php
/*
 */
class UtilMultiLanguageShopName extends SOYShopShopNameBase{

	function getForm(){
		SOY2::import("module.plugins.util_multi_language.util.UtilMultiLanguageUtil");

		$html = array();
		foreach(UtilMultiLanguageUtil::allowLanguages() as $lang => $title){
			if($lang == "jp") continue;
			$shopName = SOYShop_DataSets::get("config.shop.name.".$lang, "");
			$html[] = "<tr>";
			$html[] = "<th>ショップ名(" . $lang . ")</th>";
			$html[] = "<td><input name=\"LanguageConfig[".$lang."]\" value=\"" . $shopName . "\" type=\"text\" class=\"form-control\"></td>";
			$html[] = "</tr>";
		}

		return implode("\n", $html);
	}

	function doPost(){
		if(isset($_POST["LanguageConfig"]) && is_array($_POST["LanguageConfig"])){
			foreach($_POST["LanguageConfig"] as $lang => $name){
				$name = trim($name);
				if(!strlen($name)) $name = null;
				SOYShop_DataSets::put("config.shop.name.".$lang, $name);
			}
		}
	}
}
SOYShopPlugin::extension("soyshop.shop.name", "util_multi_language", "UtilMultiLanguageShopName");
