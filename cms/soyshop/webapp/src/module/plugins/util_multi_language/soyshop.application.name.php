<?php
SOY2::import("module.plugins.util_multi_language.util.UtilMultiLanguageUtil");
class UtilMultiLanguageApplicationName extends SOYShopApplicationNameBase{

	/**
	 * @return string
	 */
	function getFormFromCartApplicationConfigPage(){

		$html = array();
		foreach(UtilMultiLanguageUtil::allowLanguages() as $lang => $title){
			if($lang == UtilMultiLanguageUtil::LANGUAGE_JP) continue;

			$html[] = "<dt>カートのタイトル(" . $lang . ")</dt>";
			$html[] = "<dd>";
			$html[] = "<input name=\"language[" . $lang . "]\" value=\"" . UtilMultiLanguageUtil::getPageTitle($this->getMode(), $lang) . "\" type=\"text\" class=\"title\" />";
			$html[] = "</dd>";
		}

		return implode("\n", $html);
	}

	/**
	 * doPost
	 */
	function doPostFromCartApplicationConfigPage(){
		self::save();
	}

	/**
	 * @return string
	 */
	function getFormFromMypageApplicationConfigPage(){

		$html = array();
		foreach(UtilMultiLanguageUtil::allowLanguages() as $lang => $title){
			if($lang == UtilMultiLanguageUtil::LANGUAGE_JP) continue;

			$html[] = "<dt>マイページのタイトル(" . $lang . ")</dt>";
			$html[] = "<dd>";
			$html[] = "<input name=\"language[" . $lang . "]\" value=\"" . UtilMultiLanguageUtil::getPageTitle($this->getMode(), $lang) . "\" type=\"text\" class=\"title\" />";
			$html[] = "</dd>";
		}

		return implode("\n", $html);
	}

	/**
	 * doPost
	 */
	function doPostFromMypageApplicationConfigPage(){
		self::save();
	}

	private function save(){
		if($_POST["language"]){
			foreach($_POST["language"] as $lang => $value){
				UtilMultiLanguageUtil::savePageTitle($this->getMode(), $lang, $value);
			}
		}
	}
}
SOYShopPlugin::extension("soyshop.application.name", "util_multi_language", "UtilMultiLanguageApplicationName");
