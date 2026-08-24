<?php

class CanonicalUrlConfigPage extends WebPage {

	private $pluginObj;

	function __construct(){

	}

	function doPost(){
		if(soy2_check_token()){
			$this->pluginObj->setIsTrailingSlash($_POST["is_trailing_slash"]);
			$this->pluginObj->setIsWww($_POST["is_www"]);

			$isShortLink = (isset($_POST["is_short_link"]) && (int)$_POST["is_short_link"] === 1) ? 1 : 0;
			$this->pluginObj->setIsShortLink($isShortLink);

			$isQuery = (isset($_POST["is_query"]) && (int)$_POST["is_query"] === 1) ? 1 : 0;
			$this->pluginObj->setIsQuery($isQuery);

			CMSPlugin::savePluginConfig($this->pluginObj->getId(), $this->pluginObj);
			CMSPlugin::redirectConfigPage();
		}
	}

	function execute(){
		parent::__construct();

		$this->addForm("form");

		$this->addCheckBox("is_trailing_slash", array(
			"name" => "is_trailing_slash",
			"value" => 1,
			"selected" => ($this->pluginObj->getIsTrailingSlash() == 1),
			"label" => "あり"
		));

		$this->addCheckBox("no_trailing_slash", array(
			"name" => "is_trailing_slash",
			"value" => 0,
			"selected" => ($this->pluginObj->getIsTrailingSlash() != 1),
			"label" => "なし"
		));

		$this->addCheckBox("is_www", array(
			"name" => "is_www",
			"value" => 1,
			"selected" => ($this->pluginObj->getIsWww() == 1),
			"label" => "あり"
		));

		$this->addCheckBox("no_www", array(
			"name" => "is_www",
			"value" => 0,
			"selected" => ($this->pluginObj->getIsWww() != 1),
			"label" => "なし"
		));

		$this->addCheckBox("is_short_link", array(
			"name" => "is_short_link",
			"value" => 1,
			"selected" => ($this->pluginObj->getIsShortLink() == 1),
			"label" => "shortlinkメタタグを挿入する"
		));

		$this->addCheckBox("is_query", array(
			"name" => "is_query",
			"value" => 1,
			"selected" => ($this->pluginObj->getIsQuery() == 1),
			"label" => "サイト内検索のクエリを追加する"
		));
	}

	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}
}
