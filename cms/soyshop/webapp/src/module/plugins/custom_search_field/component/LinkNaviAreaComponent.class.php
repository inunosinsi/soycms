<?php

class LinkNaviAreaComponent {

	public static function build(){
		$isTop = (count($_GET) === 1);
		$isCustom = (isset($_GET["custom"]));
		$isCategory = (isset($_GET["category"]));
		$isExImport = (isset($_GET["eximport"]));
		$isConfig = (isset($_GET["config"]));

		$html = array();
		$html[] = "<div class=\"text-right\">";

		if($isTop || $isCustom || $isCategory){
			$html[] = "	<a class=\"btn btn-primary\" href=\"javascript:void(0);\" data-toggle=\"modal\" data-target=\"#customfieldModal\">項目の追加</a>";
		}

		if(!$isTop) $html[] = "	<a class=\"btn btn-info\" href=\"" . SOY2PageController::createLink("Config.Detail?plugin=custom_search_field") . "\">カスタムサーチフィールド</a>";
		if(!$isCustom) $html[] = "	<a class=\"btn btn-info\" href=\"" . SOY2PageController::createLink("Config.Detail?plugin=custom_search_field&custom") . "\">サーチフィールドのカスタムフィールド</a>";
		if(!$isCategory) $html[] = "	<a class=\"btn btn-info\" href=\"" . SOY2PageController::createLink("Config.Detail?plugin=custom_search_field&category") . "\">カテゴリカスタムサーチフィールド</a>";
		if(!$isExImport) $html[] = "	<a class=\"btn btn-default\" href=\"" . SOY2PageController::createLink("Config.Detail?plugin=custom_search_field&eximport") . "\">カスタムフィールド</a>";
		if(!$isConfig) $html[] = "	<a class=\"btn btn-success\" href=\"" . SOY2PageController::createLink("Config.Detail?plugin=custom_search_field&config") . "\">検索の設定</a>";
		$html[] = "</div>";
		return implode("\n", $html);
	}
}
