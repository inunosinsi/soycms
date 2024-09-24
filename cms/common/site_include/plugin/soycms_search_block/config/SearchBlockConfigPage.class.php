<?php
class SearchBlockConfigPage extends WebPage{
	
	private $pluginObj;
	
	function __construct(){
		SOY2::import("domain.cms.DataSets");
	}
	
	function doPost(){
		if(soy2_check_token()){
			$logic = SOY2Logic::createInstance("site_include.plugin.soycms_search_block.logic.GeminiSearchLogic");
			$logic->saveApiKey($_POST["Config"]["gemini_api_key"]);
			$logic->savePromptFormat($_POST["Config"]["gemini_api_prompt_format"]);
			CMSPlugin::redirectConfigPage();
		}

	}
	
	function execute(){
		parent::__construct();

		self::_buildGeminiConfigArea();
	}

	private function _buildGeminiConfigArea(){
		$logic = SOY2Logic::createInstance("site_include.plugin.soycms_search_block.logic.GeminiSearchLogic");

		$this->addForm("form");

		$this->addInput("gemini_api_key", array(
			"name" => "Config[gemini_api_key]",
			"value" => $logic->getApiKey(),
			"style" => "width:60%"
		));

		$this->addInput("gemini_api_prompt_format", array(
			"name" => "Config[gemini_api_prompt_format]",
			"value" => $logic->getPromptFormat(),
			"style" => "width:60%"
		));

		$this->addLabel("gemini_api_prompt_format_example", array(
			"text" => $logic->buildPromptExample()
		));
	}
	
	function setPluginObj(SOYCMS_Search_Block_Plugin $pluginObj) {
		$this->pluginObj = $pluginObj;
	}
}
