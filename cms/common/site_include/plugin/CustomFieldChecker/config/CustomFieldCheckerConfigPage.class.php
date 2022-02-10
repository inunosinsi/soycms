<?php

class CustomFieldCheckerConfigPage extends WebPage {

	private $pluginObj;

	function __construct(){}

	function doPost(){
		if(soy2_check_token()){
			
		}
	}

	function execute(){
		parent::__construct();

		self::_buildCustomForm("CustomField", "customfield");
		self::_buildCustomForm("CustomFieldAdvanced", "customfield_advanced");
	}

	private function _buildCustomForm(string $pluginId, string $idx){
		$this->addForm($idx . "_form");

		//カスタムフィールドが有効か？
		$isActived = self::_checkActivedPlugin($pluginId);
		DisplayPlugin::toggle($idx, $isActived);

		$opts = ($isActived) ? self::logic()->getConfig($pluginId) : array();
		$fieldId = (isset($_POST[$idx])) ? $_POST[$idx] : "";

		$this->addSelect($idx, array(
			"name" => $idx,
			"options" => $opts,
			"selected" => $fieldId
		));

		$this->addInput($idx . "_keyword", array(
			"name" => $idx . "_keyword",
			"value" => (isset($_POST[$idx . "_keyword"])) ? $_POST[$idx . "_keyword"] : "",
			"attr:placeholder" => "値で検索"
		));

		$fieldLabel = (strlen($fieldId) && isset($opts[$fieldId])) ? $opts[$fieldId] : "";

		$this->addLabel($idx . "_result", array(
			"html" => self::_buildResult($pluginId, $idx, $fieldLabel)
		));

		$this->addForm($idx . "_entry_list_form");
	}

	private function _buildResult(string $pluginId, string $idx, string $fieldLabel){
		if(!isset($_POST[$idx]) || !strlen($_POST[$idx]) || !strlen($fieldLabel)) return "";

		$q = (isset($_POST[$idx . "_keyword"])) ? (string)$_POST[$idx . "_keyword"] : "";

		$html = array();
		list($haves, $nones) = self::logic()->get($pluginId, $_POST[$idx], $q);
		if(is_array($nones) && count($nones)){
			$html[] = "<div class=\"alert alert-warning\">値が登録されていない記事一覧</div>";
			$html[] = self::_buildHtml($nones, $idx, $fieldLabel);
		}
		if(is_array($haves) && count($haves)){
			$html[] = "<div class=\"alert alert-info\">値が登録されている記事一覧</div>";
			$html[] = self::_buildHtml($haves, $idx, $fieldLabel);
		}
		return implode("\n", $html);
	}

	private function _buildHtml(array $array, string $idx, string $fieldLabel){
		$html = array();
		$html[] = "<ul style=\"list-style-type: none;\">";
		foreach($array as $entryId => $title){
			//$html[] = "<li><input type=\"checkbox\" class=\"" . $idx . "_check\" name=\"Entry[]\" value=\"" . $entryId . "\"><a href=\"" . SOY2PageController::createLink("Entry.Detail." . $entryId). "\">" . $title . "</a></li>";
			$html[] = "<li><a href=\"" . SOY2PageController::createLink("Entry.Detail." . $entryId). "\">" . $title . "</a></li>";
		}
		$html[] = "</ul>";
		
		// $html[] = "<div id=\"" . $idx . "_btn_area\" class=\"form-inline\" style=\"padding:5px;\">";
		// $html[] = "<input type=\"text\" class=\"form-control\">";
		// $html[] = "<input type=\"submit\" name=\"insert\" class=\"btn btn-primary\" value=\"チェックを入れた記事の「" . $fieldLabel . "」フィールドに値を挿入する\">";
		// $html[] = "<input type=\"submit\" name=\"remove\" class=\"btn btn-danger\" value=\"チェックを入れた記事の「" . $fieldLabel . "」フィールドの値を削除する\" onclick=\"return confirm('削除しますがよろしいですか？');\">";
		// $html[] = "</div>";
		return implode("\n", $html);
	}

	private function _checkActivedPlugin(string $pluginId){
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
