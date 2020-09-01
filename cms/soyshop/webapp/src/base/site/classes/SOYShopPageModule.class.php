<?php

class SOYShopPageModulePlugin extends PluginBase{

	protected $_soy2_prefix = "shop";

	function execute(){
		$soyValue = $this->soyValue;
		$array = explode(".", $soyValue);

		//隠しモード：別のサイトのモジュールを取得する
		$siteId = null;
		if(count($array) && preg_match('/\{(.*)\}/', $array[0], $tmp)){
			$dust = array_shift($array);	//配列を一つずらす
			unset($dust);
			if(isset($tmp[1])) $siteId = $tmp[1];
			$soyValue = str_replace("{" . $siteId . "}.", "", $soyValue);
		}
		//隠しモードここまで

		if(count($array) > 1) unset($array[0]);
		$func = "soyshop_" . implode("_", $array);

		$siteDir = SOYSHOP_SITE_DIRECTORY;

		//隠しモード用にパスの書き換え
		if(!is_null($siteId)) $siteRoot = substr($siteRoot, 0, strrpos($siteRoot, "/")) . "/" . $siteId;
		$modulePath = $siteDir . ".module/" . str_replace(".", "/", $soyValue) . ".php";

		$this->setInnerHTML(
		'<?php '.// サイト/.module/にファイルがあればそれを優先して使う。なければ、SOY Shopのmodule/site/以下のファイルを使う。ファイルがないか実行すべき関数が定義されてなければ何もしない（またはデバッグ用出力を行う）。
			'if(file_exists("' . $modulePath . '")){' .'include_once("' . $modulePath . '");' .'}else{' .'SOY2::import("module.site.' . $soyValue . '",".php");' .'}' .'if(function_exists("' . $func . '")){' .'ob_start(); ' .' ?>' .
		$this->getInnerHTML() . '' .
		'<?php ' .
				'$tmp_html=ob_get_contents();ob_end_clean(); ' .
				'echo call_user_func("' . $func . '",$tmp_html,$this);' .
			'}elseif(defined("DEBUG_MODE") && DEBUG_MODE){' .
				'echo "function not found : ' . $func . '";' .
			'}' .
		' ?>'
		);
	}
}
