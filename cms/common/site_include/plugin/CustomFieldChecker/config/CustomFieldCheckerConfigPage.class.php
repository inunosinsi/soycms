<?php

class CustomFieldCheckerConfigPage extends WebPage {

	private $pluginObj;

	function __construct(){}

	function doPost(){}

	function execute(){
		parent::__construct();

		self::_buildCustomForm("CustomField", "customfield");
		self::_buildCustomForm("CustomFieldAdvanced", "customfield_advanced");
	}

	private function _buildCustomForm($pluginId, $idx){
		$this->addForm($idx . "_form");

		//カスタムフィールドが有効か？
		$isActived = self::_checkActivedPlugin($pluginId);
		DisplayPlugin::toggle($idx, $isActived);

		$this->addSelect($idx, array(
			"name" => $idx,
			"options" => ($isActived) ? self::logic()->getConfig($pluginId) : array(),
			"selected" => (isset($_POST[$idx])) ? $_POST[$idx] : ""
		));

		$this->addLabel($idx . "_result", array(
			"html" => self::_buildResult($pluginId, $idx)
		));
	}

	private function _buildResult($pluginId, $idx){
		if(!isset($_POST[$idx]) || !strlen($_POST[$idx])) return "";

		$html = array();
		list($haves, $nones) = self::logic()->get($pluginId, $_POST[$idx]);
		if(is_array($nones) && count($nones)){
			$html[] = "<div class=\"alert alert-warning\">値が登録されていない記事一覧</div>";
			$html[] = self::_buildHtml($nones);
		}
		if(is_array($haves) && count($haves)){
			$html[] = "<div class=\"alert alert-info\">値が登録されている記事一覧</div>";
			$html[] = self::_buildHtml($haves);
		}
		return implode("\n", $html);
	}

	private function _buildHtml($array){
		$html = array();
		$html[] = "<ul>";
		foreach($array as $entryId => $title){
			$html[] = "<li><a href=\"" . SOY2PageController::createLink("Entry.Detail." . $entryId). "\">" . $title . "</a></li>";
		}
		$html[] = "</ul>";
		return implode("\n", $html);
	}

	private function _checkActivedPlugin($pluginId){
		return file_exists(UserInfoUtil::getSiteDirectory() . ".plugin/" . $pluginId . ".active");
	}

	private function logic(){
		static $logic;
		if(is_null($logic)) $logic = SOY2Logic::createInstance("site_include.plugin.CustomFieldChecker.logic.CheckLogic");
		return $logic;
	}

	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}
}
