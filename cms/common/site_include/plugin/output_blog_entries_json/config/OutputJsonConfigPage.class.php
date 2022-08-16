<?php

class OutputJsonConfigPage extends WebPage {

	private $pluginObj;

	function __construct(){}

	function execute(){
		parent::__construct();

		//一番若いブログページのIDを取得する
		$oldestPageId = self::_getOldestBlogPageId();
		$url = UserInfoUtil::getSitePublishURL();
		
		$this->addLabel("url", array(
			"text" => $url
		));

		DisplayPlugin::toggle("example", ($oldestPageId > 0));
		$this->addLabel("id_example", array(
			"text" => $oldestPageId
		));
		$exampleUrl = $url . $oldestPageId . ".json";
		$this->addLink("json_output_url_example", array(
			"link" => $exampleUrl . "?limit=10",
			"text" => $exampleUrl
		));

		DisplayPlugin::toggle("example_usage_pager", ($oldestPageId > 0));

		for($i = 0; $i < 2; $i++){
			$this->addLink("json_output_pager_" . $i . "_example", array(
				"link" => $exampleUrl . "?limit=5&offset=" . $i,
				"text" => $exampleUrl . "?limit=5&offset=" . $i
			));
		}
		
		//カスタムフィールド
		self::_customfieldExample($exampleUrl);
	}

	/**
	 * ページIDが最も小さいブログページのIDを取得　なければ0にする
	 */
	private function _getOldestBlogPageId(){
		$dao = new SOY2DAO();
		$sql = "SELECT id FROM Page WHERE page_type = 200 ORDER BY id ASC LIMIT 1";
		try{
			$res = $dao->executeQuery($sql);
		}catch(Exception $e){
			$res = array();
		}
		if(!count($res)) return 0;
		
		return (isset($res[0]["id"]) && is_numeric($res[0]["id"])) ? (int)$res[0]["id"] : 0;
	}

	private function _customfieldExample(string $exampleUrl){
		$fieldIds = self::_getCustomdFieldConfig();
		DisplayPlugin::toggle("customfield_example", count($fieldIds));
		
		$fieldIdOne = (isset($fieldIds[0])) ? $fieldIds[0] : "";

		// フィールドの指定が1個の場合
		$this->addLabel("field_only", array(
			"text" => $fieldIdOne
		));

		$this->addLink("field_only_url", array(
			"link" => $exampleUrl . "?limit=5&customfield=" . $fieldIdOne,
			"text" => $exampleUrl . "?limit=5&customfield=" . $fieldIdOne
		));

		DisplayPlugin::toggle("customfield_example_two", count($fieldIds) > 1);

		$fieldIdTwo = (isset($fieldIds[1])) ? $fieldIds[1] : "";

		$this->addLabel("field_two", array(
			"text" => $fieldIdOne . " と " . $fieldIdTwo
		));

		$this->addLink("field_two_url", array(
			"link" => $exampleUrl . "?limit=5&customfield[]=" . $fieldIdOne . "&customfield[]=" . $fieldIdTwo,
			"text" => $exampleUrl . "?limit=5&customfield[]=" . $fieldIdOne . "&customfield[]=" . $fieldIdTwo
		));
	}

	private function _getCustomdFieldConfig(){
		if(!CMSPlugin::activeCheck("CustomFieldAdvanced")) return array();

		SOY2::import("site_include.plugin.CustomFieldPluginAdvanced.CustomFieldPluginAdvanced", ".php");
		$customfields = CMSPlugin::loadPluginConfig(CustomFieldPluginAdvanced::PLUGIN_ID)->customFields;
		if(!is_array($customfields) || !count($customfields)) return array();

		$fieldIds = array();
		foreach($customfields as $fieldId => $_dust){
			$fieldIds[] = $fieldId;
		}

		return $fieldIds;
	}

	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}
}