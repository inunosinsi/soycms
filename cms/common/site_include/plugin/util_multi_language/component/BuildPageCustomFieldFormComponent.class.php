<?php

class BuildPageCustomFieldFormComponent {

	private $pluginObj;

	/**
	 * @paran int
	 * @return string
	 */
	function buildForm(int $pageId){
		$langs = SOYCMSUtilMultiLanguageUtil::getLanguageList($this->pluginObj);
		if(!count($langs)) return "";

		$html = array();

		foreach($langs as $lang){
			if($lang === SOYCMSUtilMultiLanguageUtil::LANGUAGE_JP) continue;

			$title = (string)soycms_get_page_attribute_object($pageId, SOYCMSUtilMultiLanguageUtil::LANGUAGE_FIELD_KEY.$lang)->getValue();

			$html[] = "<div class=\"form-group\">";
			$html[] = "	<label>ページのタイトル(".$lang.")</label>";
			$html[] = "	<input class=\"form-control\" type=\"text\" name=language[".$lang."] value=\"".$title."\">";
			$html[] = "</div>";
		}

		return implode("\n", $html);
	}

	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}
}