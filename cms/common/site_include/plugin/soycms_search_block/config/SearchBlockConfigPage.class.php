<?php
class SearchBlockConfigPage extends WebPage{
	
	private $pluginObj;
	
	function __construct(){
		SOY2::import("domain.cms.DataSets");
		SOY2::import("site_include.plugin.soycms_search_block.util.SearchBlockUtil");
	}
	
	function doPost(){
		if(soy2_check_token()){
			SearchBlockUtil::saveNormalizeConfig($_POST["Config"]["normalize"]);
			SearchBlockUtil::saveSecurityConfig($_POST["Security"]);
		
			$logic = SOY2Logic::createInstance("site_include.plugin.soycms_search_block.logic.GeminiSearchLogic");
			$logic->saveApiKey($_POST["Config"]["gemini_api_key"]);
			$logic->savePromptFormat($_POST["Config"]["gemini_api_prompt_format"]);
			CMSPlugin::redirectConfigPage();
		}

	}
	
	function execute(){
		parent::__construct();

		$this->addForm("form");

		self::_buildNomarlizeConfigArea();
		self::_buildSecurityConfigArea();
		self::_buildGeminiConfigArea();
	}

	private function _buildNomarlizeConfigArea(){
		$cfg = SearchBlockUtil::getNormalizeConfig();

		foreach(array("katakana", "number") as $idx){
			$this->addCheckBox("no_normalize_".$idx, array(
				"type" => "radio",
				"name" => "Config[normalize][".$idx."]",
				"value" => SearchBlockUtil::NORMALIZE_NONE,
				"label" => "しない",
				"selected" => (!isset($cfg[$idx]) || (int)$cfg[$idx] === SearchBlockUtil::NORMALIZE_NONE)
			));

			$this->addCheckBox("normalize_".$idx."_half_to_full", array(
				"type" => "radio",
				"name" => "Config[normalize][".$idx."]",
				"value" => SearchBlockUtil::NORMALIZE_HALF_TO_FULL,
				"label" => "半角→全角",
				"selected" => (isset($cfg[$idx]) && (int)$cfg[$idx] === SearchBlockUtil::NORMALIZE_HALF_TO_FULL)
			));

			$this->addCheckBox("normalize_".$idx."_full_to_half", array(
				"type" => "radio",
				"name" => "Config[normalize][".$idx."]",
				"value" => SearchBlockUtil::NORMALIZE_FULL_TO_HALF,
				"label" => "全角→半角",
				"selected" => (isset($cfg[$idx]) && (int)$cfg[$idx] === SearchBlockUtil::NORMALIZE_FULL_TO_HALF)
			));
		}
	}

	private function _buildSecurityConfigArea(){
		$cfg = SearchBlockUtil::getSecurityConfig();

		$this->addCheckBox("is_strip_tags", array(
			"type" => "checkbox",
			"name" => "Security[is_strip_tags]",
			"value" => SearchBlockUtil::ON,
			"label" => "検索クエリからHTMLタグを削除する",
			"selected" => (isset($cfg["is_strip_tags"]) && (int)$cfg["is_strip_tags"] === SearchBlockUtil::ON)
		));

		$this->addCheckBox("deny_url", array(
			"type" => "checkbox",
			"name" => "Security[deny_url]",
			"value" => SearchBlockUtil::ON,
			"label" => "検索クエリでURLを禁止",
			"selected" => (isset($cfg["deny_url"]) && (int)$cfg["deny_url"] === SearchBlockUtil::ON)
		));

		$this->addCheckBox("deny_path", array(
			"type" => "checkbox",
			"name" => "Security[deny_path]",
			"value" => SearchBlockUtil::ON,
			"label" => "検索クエリでファイルパスを禁止",
			"selected" => (isset($cfg["deny_path"]) && (int)$cfg["deny_path"] === SearchBlockUtil::ON)
		));

		$this->addInput("limit_char", array(
			"type" => "number",
			"name" => "Security[limit_char]",
			"value" => (isset($cfg["limit_char"])) ? (int)$cfg["limit_char"] : 0,
			"style" => "width:60px;"
		));
	}

	private function _buildGeminiConfigArea(){
		$logic = SOY2Logic::createInstance("site_include.plugin.soycms_search_block.logic.GeminiSearchLogic");

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
