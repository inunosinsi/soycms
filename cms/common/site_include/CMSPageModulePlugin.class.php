<?php

class CMSPageModulePlugin extends PluginBase{

	protected $_soy2_prefix = "cms";

	function execute(){
		$soyValue = $this->soyValue;
		$array = explode(".", $soyValue);

		/**
		 * 隠しモード：別のサイトのモジュールを取得する
		 * 別サイトのモジュールを呼び出す時は<!-- cms:module="{siteId}.****" -->にする
		 */
		$siteId = null;
		if(count($array) && preg_match('/\{(.*)\}/', $array[0], $tmp)){
			$dust = array_shift($array);	//配列を一つずらす
			unset($dust);
			if(isset($tmp[1])) $siteId = $tmp[1];
			$soyValue = str_replace("{" . $siteId . "}.", "", $soyValue);
		}
		//隠しモードここまで

		if(count($array) > 1) unset($array[0]);
		$func = "soycms_" . implode("_", $array);

		//ダイナミック編集のためにここで定義を確認しておく
		if(!defined("_SITE_ROOT_")) define("_SITE_ROOT_", UserInfoUtil::getSiteDirectory());
		$siteRoot = _SITE_ROOT_;

		//隠しモード用にパスの書き換え
		if(is_string($siteId)) {

			/** 元サイトのサイトルートを移動している時対策 **/
			
			$try = 0;
			$tmp = dirname($siteRoot) . "/";
			for(;;){
				if($try++ > 2) break;
				//任意のディレクトリに.modulesディレクトリがあるか？
				$dir = $tmp . $siteId . "/.module/";
				if(file_exists($dir) && is_dir($dir)){
					$siteRoot = $tmp . $siteId;
					break;
				}
				$tmp = dirname($tmp) . "/";
			}
		}

		$modulePath = soy2_realpath($siteRoot) . ".module/" . str_replace(".", "/", $soyValue) . ".php";

		$this->setInnerHTML(
		'<?php '.// サイト/.module/にファイルがあればそれを優先して使う。なければ、SOY CMSのmodule/site/以下のファイルを使う。ファイルがないか実行すべき関数が定義されてなければ何もしない（またはデバッグ用出力を行う）。
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
