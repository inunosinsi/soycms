<?php
SOY2::import("module.plugins.util_multi_language.util.UtilMultiLanguageUtil");
class UtilMultiLanguageApplicationName extends SOYShopApplicationNameBase{

	const MODE_CART = 0;
	const MODE_MYPAGE = 1;

	/**
	 * @return string
	 */
	function getFormFromCartApplicationConfigPage(){
		$html = array();
		foreach(UtilMultiLanguageUtil::allowLanguages() as $lang => $title){
			if($lang == UtilMultiLanguageUtil::LANGUAGE_JP) continue;
			$html[] = self::_buildItem($lang);
		}

		return implode("\n", $html);
	}

	/**
	 * doPost
	 */
	function doPostFromCartApplicationConfigPage(){
		self::_save();
	}

	/**
	 * @return string
	 */
	function getFormFromMypageApplicationConfigPage(){

		$html = array();
		foreach(UtilMultiLanguageUtil::allowLanguages() as $lang => $title){
			if($lang == UtilMultiLanguageUtil::LANGUAGE_JP) continue;
			$html[] = self::_buildItem($lang);
		}

		return implode("\n", $html);
	}

	/**
	 * doPost
	 */
	function doPostFromMypageApplicationConfigPage(){
		self::_save();
	}

	/**
	 * @param string
	 * @return string
	 */
	private function _buildItem(string $lang){
		$html = array();

		$title = ($this->getMode() == "cart") ? "カート" : "マイページ";

		$html[] = "<div class=\"form-group\">";
		$html[] = "<label>".$title."のタイトル(" . $lang . ")</label>";
		$html[] = "<input name=\"language[" . $lang . "]\" value=\"" . UtilMultiLanguageUtil::getPageTitle($this->getMode(), $lang) . "\" type=\"text\" class=\"form-control\">";
		$html[] = "</div>";

		return implode("\n", $html);
	}

	private function _save(){
		if($_POST["language"]){
			foreach($_POST["language"] as $lang => $value){
				UtilMultiLanguageUtil::savePageTitle($this->getMode(), $lang, $value);
			}
		}
	}
}
SOYShopPlugin::extension("soyshop.application.name", "util_multi_language", "UtilMultiLanguageApplicationName");
