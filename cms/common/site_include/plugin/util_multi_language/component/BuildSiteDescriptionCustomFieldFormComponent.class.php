<?php

class BuildSiteDescriptionCustomFieldFormComponent {

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

			$description = (string)DataSets::get(SOYCMSUtilMultiLanguageUtil::LANGUAGE_SITE_DESCRIPTION_KEY.$lang, "");
			
			$html[] = "<div class=\"form-group\">";
			$html[] = "	<label>サイトの説明(".$lang.")</label>";
			$html[] = "	<textarea class=\"form-control\" name=\"language[description][".$lang."]\" style=\"height:200px;\">".$description."</textarea>";
			$html[] = "</div>";
		}

		return implode("\n", $html);
	}

	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}
}