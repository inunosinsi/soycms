<?php

class CMSPageModulePlugin extends PluginBase{

	protected $_soy2_prefix = "cms";

	function execute(){
		$soyValue = $this->soyValue;

		$array = explode(".", $soyValue);
		if(count($array) > 1){
			unset($array[0]);
		}
		$func = "soycms_" . implode("_", $array);

		//ダイナミック編集のためにここで定義を確認しておく
		if(!defined("_SITE_ROOT_")) define("_SITE_ROOT_", UserInfoUtil::getSiteDirectory());
		$modulePath = soy2_realpath(_SITE_ROOT_) . ".module/" . str_replace(".", "/", $soyValue) . ".php";

		//ファイルが見つからなければ、UserInfoUtil::getSiteDirectory()の方も試す
		// if(!file_exists($modulePath) && _SITE_ROOT_ != UserInfoUtil::getSiteDirectory()){
		// 	$modulePath = soy2_realpath(UserInfoUtil::getSiteDirectory()) . ".module/" . str_replace(".", "/", $soyValue) . ".php";
		// }

		$this->setInnerHTML(
		'<?php '.// サイト/.module/にファイルがあればそれを優先して使う。なければ、SOY Shopのmodule/site/以下のファイルを使う。ファイルがないか実行すべき関数が定義されてなければ何もしない（またはデバッグ用出力を行う）。
			'if(file_exists("' . $modulePath . '")){' .'include_once("' . $modulePath . '");' .'}else{' .'SOY2::import("site_include.module.' . $soyValue . '",".php");' .'}' .'if(function_exists("' . $func . '")){' .'ob_start(); ' .' ?>' .
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
