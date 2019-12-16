<?php
/*
 * Created on 2009/02/10
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
//拡張設定画面
class SOYShopConfigPageBase implements SOY2PluginAction{

	private $moduleId;

	/**
	 * @return string
	 * プラグイン毎の詳細ページにコンテンツを表示
	 */
	function getConfigPage(){
		return "";
	}

	/**
	 * @return string
	 * プラグイン毎の詳細ページのタイトルを表示
	 */
	function getConfigPageTitle(){
		return "";
	}

	/**
	 * @return string
	 * プラグインの説明を表示
	 */
	function getConfigPageDescription(){
		return "";
	}

	/**
	 * プラグイン毎の詳細ページにリダイレクトする
	 */
	function redirect($query = ""){
		if(strlen($query) > 0) $query = "&" . $query;
		SOY2PageController::jump("Config.Detail?plugin=" . $this->moduleId . $query);
	}

	function getModuleId() {
		return $this->moduleId;
	}
	function setModuleId($moduleId) {
		$this->moduleId = $moduleId;
	}
}
class SOYShopConfigPageDelegateAction implements SOY2PluginDelegateAction{

	private $_list = array();
	private $mode = "list";
	private $action;

	function run($extetensionId,$moduleId,SOY2PluginAction $action){

		$action->setModuleId($moduleId);

		switch($this->mode){

			case "list":
				$this->_list[$moduleId] = array(
					"title" => $action->getConfigPageTitle(),
					"description" => $action->getConfigPageDescription()
				);
				break;

			case "config":
				$this->action = $action;
				break;

		}
	}

	function getList(){
		return $this->_list;
	}


	function getMode() {
		return $this->mode;
	}
	function setMode($mode) {
		$this->mode = $mode;
	}

	function getConfigPage(){
		return $this->action->getConfigPage();
	}

	function getTitle(){
		return $this->action->getConfigPageTitle();
	}
}
SOYShopPlugin::registerExtension("soyshop.config","SOYShopConfigPageDelegateAction");
