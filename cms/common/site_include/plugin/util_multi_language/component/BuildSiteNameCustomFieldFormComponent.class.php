<?php

class BuildSiteNameCustomFieldFormComponent {

	private $pluginObj;

	/**
	 * @return string
	 */
	function buildForm(){
		$langs = SOYCMSUtilMultiLanguageUtil::getLanguageList($this->pluginObj);
		if(!count($langs)) return "";

		SOY2DAOFactory::importEntity("cms.DataSets");

		$html = array();

		foreach($langs as $lang){
			if($lang === SOYCMSUtilMultiLanguageUtil::LANGUAGE_JP) continue;

			$name = (string)DataSets::get(SOYCMSUtilMultiLanguageUtil::LANGUAGE_SITE_NAME_KEY.$lang, "");
						
			$html[] = "<div class=\"form-group\">";
			$html[] = "	<label>サイト名称(".$lang.")</label>";
			$html[] = "	<input class=\"form-control\" type=\"text\" name=\"language[name][".$lang."]\" value=\"".$name."\">";
			$html[] = "</div>";
		}

		return implode("\n", $html);
	}

	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}
}