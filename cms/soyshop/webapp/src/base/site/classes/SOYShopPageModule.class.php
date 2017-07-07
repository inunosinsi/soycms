<?php

class SOYShopPageModulePlugin extends PluginBase{

	protected $_soy2_prefix = "shop";

	function execute(){
		$soyValue = $this->soyValue;

		$array = explode(".", $soyValue);
		if(count($array) > 1){
			unset($array[0]);
		}
		$func = "soyshop_" . implode("_", $array);

		$modulePath = SOYSHOP_SITE_DIRECTORY . ".module/" . str_replace(".", "/", $soyValue) . ".php";

		$this->setInnerHTML(
		'<?php '.// サイト/.module/にファイルがあればそれを優先して使う。なければ、SOY Shopのmodule/site/以下のファイルを使う。ファイルがないか実行すべき関数が定義されてなければ何もしない（またはデバッグ用出力を行う）。
			'if(file_exists("' . $modulePath . '")){' .'include_once("' . $modulePath . '");' .'}else{' .'SOY2::import("module.site.' . $soyValue . '",".php");' .'}' .'if(function_exists("' . $func . '")){' .'ob_start(); ' .' ?>' .
		$this->getInnerHTML() . '' .
		'<?php ' .
				'$tmp_html=ob_get_contents();ob_end_clean(); ' .
				'echo call_user_func("' . $func . '",$tmp_html,$this);' .
			'}elseif(DEBUG_MODE){' .
				'echo "function not found : ' . $func . '";' .
			'}' .
		' ?>'
		);
	}
}
