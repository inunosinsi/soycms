<?php

class SwitchPageConfigPage extends WebPage {

	private $pluginObj;

	function __construct(){
		SOY2::import("site_include.plugin.switch_page.util.SwitchPageUtil");
		SOY2::import("site_include.plugin.switch_page.component.SwitchPageConfigListComponent");
	}

	function doPost(){
		if(soy2_check_token()){

			/**
			 * 設定内容の保存
			 * データは二つに分ける
			 * DataSetに設定の詳細
			 * $this->setFroms()と$this->setTo_es()で、array(idx => fromのuri)の形式でデータを格納する
			 */
			$cnfs = SwitchPageUtil::getConfig();

			//新たに内容を追加する
			$cnf = $_POST["Config"];

			//startとendを変換する
			foreach(array("start", "end") as $t){
				$cnf[$t] = SwitchPageUtil::convertDateStringToTimestamp($cnf[$t], $t);
			}

			$cnfs[] = $cnf;

			//切り替え先ページ設定
			list($fromList, $toList) = self::_createUriList($cnfs);
			$this->pluginObj->setFromList($fromList);
			$this->pluginObj->setToList($toList);
			SwitchPageUtil::saveConfig($cnfs);

			CMSUtil::notifyUpdate();
			CMSPlugin::savePluginConfig(SwitchPagePlugin::PLUGIN_ID, $this->pluginObj);
			CMSPlugin::redirectConfigPage();
		}
	}

	function execute(){
		if(isset($_GET["idx"]) && is_numeric($_GET["idx"]) && soy2_check_token()){
			self::_remove($_GET["idx"]);
		}

		parent::__construct();

		$cnfs = SwitchPageUtil::getConfig();
		DisplayPlugin::toggle("is_cnf", count($cnfs));

		$list = SwitchPageUtil::getPageList();

		$this->createAdd("cnf_list", "SwitchPageConfigListComponent", array(
			"list" => $cnfs,
			"pageList" => $list
		));

		$this->addForm("form");

		$this->addSelect("uri_from", array(
			"name" => "Config[from]",
			"options" => $list,
			"attr:required" => "required"
		));

		$this->addSelect("uri_to", array(
			"name" => "Config[to]",
			"options" => $list,
			"attr:required" => "required"
		));
	}

	private function _remove($idx){
		$configs = SwitchPageUtil::getConfig();
		if(isset($configs[$idx])) unset($configs[$idx]);

		$cnfs = array();
		if(count($configs)){
			foreach($configs as $cnf){
				$cnfs[] = $cnf;
			}
		}

		//切り替え先ページ設定
		list($fromList, $toList) = self::_createUriList($cnfs);
		$this->pluginObj->setFromList($fromList);
		$this->pluginObj->setToList($toList);
		SwitchPageUtil::saveConfig($cnfs);

		CMSUtil::notifyUpdate();
		CMSPlugin::savePluginConfig(SwitchPagePlugin::PLUGIN_ID, $this->pluginObj);
		SOY2PageController::jump("Plugin.Config?switch_page");
	}

	private function _createUriList($cnfs){
		if(!count($cnfs)) return array(array(), array());

		$fromList = array();
		$toList = array();
		foreach($cnfs as $idx => $cnf){
			$fromList[$idx] = SwitchPageUtil::getPageUriByPageId($cnf["from"]);

			$to = SwitchPageUtil::getPageUriByPageId($cnf["to"]);
			if(is_bool(array_search($to, $toList))) $toList[$idx] = $to;
		}

		return array($fromList, $toList);
	}

	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}
}
