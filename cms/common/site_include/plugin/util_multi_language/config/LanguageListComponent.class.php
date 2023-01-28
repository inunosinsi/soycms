<?php 

class LanguageListComponent extends HTMLList{

	private $config;
	private $smartPrefix;

	function populateItem($entity, $lang){

		$this->addLabel("language_type", array(
			"text" => $entity
		));

		$prefix = (is_string($lang)) ? self::_getPrefixString($lang) : "";
		$this->addInput("prefix_input", array(
			"name" => "Config[" . $lang . "][prefix]",
			"value" => $prefix
		));

		$this->addLabel("prefix_text", array(
			"text" => (strlen($prefix)) ? "/" . $prefix : $prefix
		));

		$this->addHidden("no_use_hidden", array(
			"name" => "Config[" . $lang . "][is_use]",
			"value" => SOYCMSUtilMultiLanguageUtil::NO_USE
		));

		$this->addCheckBox("is_use_checkbox", array(
			"name" => "Config[" . $lang . "][is_use]",
			"value" => SOYCMSUtilMultiLanguageUtil::IS_USE,
			"selected" => (is_string($lang) && self::_checkIsUse($lang)),
			"label" => " " . $entity . "サイトを表示する"
		));

		$this->addLabel("domain", array(
			"text" => $_SERVER["HTTP_HOST"]
		));

		$this->addModel("display_smartphone_config", array(
			"visible" => ($lang != "jp" && isset($this->smartPrefix))
		));

		$this->addLabel("smart_prefix", array(
			"text" => $this->smartPrefix
		));
	}

	/**
	 * @param string
	 * @return string
	 */
	private function _getPrefixString(string $lang){
		if($lang !== "jp"){
			$text = (isset($this->config[$lang]["prefix"])) ? $this->config[$lang]["prefix"] : $lang;
		}else{
			$text = (isset($this->config[$lang]["prefix"])) ? $this->config[$lang]["prefix"] : "";
		}
		return $text;
	}

	/**
	 * @param string
	 * @return string
	 */
	private function _checkIsUse(string $lang){
		if($lang == SOYCMSUtilMultiLanguageUtil::LANGUAGE_JP) return true;

		return (isset($this->config[$lang]["is_use"]) && $this->config[$lang]["is_use"] == SOYCMSUtilMultiLanguageUtil::IS_USE);
	}

	function setConfig($config){
		$this->config = $config;
	}

	function setSmartPrefix($smartPrefix){
		$this->smartPrefix = $smartPrefix;
	}
}
