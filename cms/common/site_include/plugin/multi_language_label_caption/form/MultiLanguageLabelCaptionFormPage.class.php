<?php

class MultiLanguageLabelCaptionFormPage extends WebPage {

	private $labelId;

	function __construct(){
		SOY2::import("site_include.plugin.util_multi_language.util.SOYCMSUtilMultiLanguageUtil");
	}

	function execute(){
		parent::__construct();

		$this->addLabel("language_prefix", array(
			"text" => SOYCMSUtilMultiLanguageUtil::LANGUAGE_EN
		));

		foreach(array(SOYCMSUtilMultiLanguageUtil::LANGUAGE_EN) as $lng){
			$this->addInput("caption_".$lng, array(
				"name" => "Lang[".$lng."]",
				"value" => soycms_get_label_attribute_value($this->labelId, SOYCMSUtilMultiLanguageUtil::LANGUAGE_FIELD_KEY.$lng, "string")
			));
		}
		
	}

	function setLabelId($labelId){
		$this->labelId = $labelId;
	}

}