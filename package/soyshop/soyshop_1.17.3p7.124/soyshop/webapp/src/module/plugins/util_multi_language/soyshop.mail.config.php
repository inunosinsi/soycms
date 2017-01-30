<?php

class UtilMultiLanguageMailConfig extends SOYShopMailConfig{

	/**
	 * @return Array("active", "header", "content", "footer")
	 */
	function getConfig(){
		if(!defined("SOYSHOP_MAIL_LANGUAGE")) define("SOYSHOP_MAIL_LANGUAGE", SOYSHOP_PUBLISH_LANGUAGE);
		
		if(SOYSHOP_MAIL_LANGUAGE != "jp"){
			SOY2::import("module.plugins.util_multi_language.util.UtilMultiLanguageUtil");
			return UtilMultiLanguageUtil::getMailConfig($this->getTarget(), $this->getType(), SOYSHOP_MAIL_LANGUAGE);
		}
		
		return null;
	}
	
	/**
	 * @param void
	 * @return void
	 */
	function doPost(){
		
		if(isset($_POST["Config"])){
			SOY2::import("module.plugins.util_multi_language.util.UtilMultiLanguageUtil");
			foreach($_POST["Config"] as $lang => $values){
				UtilMultiLanguageUtil::saveMailConfig($this->getTarget(), $this->getType(), $lang, $values);
			}
		}
	}
	
	/**
	 * @return html
	 */
	function buildEditForm(){
		SOY2::import("module.plugins.util_multi_language.util.UtilMultiLanguageUtil");
		$buildLogic = SOY2Logic::createInstance("module.plugins.util_multi_language.logic.BuildHTMLLogic");
		$languages = UtilMultiLanguageUtil::allowLanguages();
		$htmls = array();
		foreach($languages as $lang => $title){
			if($lang == UtilMultiLanguageUtil::LANGUAGE_JP) continue;
			$htmls[] = $buildLogic->buildHTML($this->getTarget(), $this->getType(), $lang);
		}
		
		return implode("\n", $htmls);
	}	
}
SOYShopPlugin::extension("soyshop.mail.config", "util_multi_language", "UtilMultiLanguageMailConfig");
?>