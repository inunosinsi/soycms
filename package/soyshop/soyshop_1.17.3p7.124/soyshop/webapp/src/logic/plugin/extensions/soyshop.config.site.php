<?php
//拡張設定画面
class SOYShopSiteConfigPageBase implements SOY2PluginAction{

	private $moduleId;

	/**
	 * @return string
	 */
	function getConfigPage(){

	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){

	}

	function getConfigPageDescription(){

	}

	/**
	 *
	 */
	function redirect($query = ""){
		if(strlen($query) > 0)$query = "&" . $query;
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
SOYShopPlugin::registerExtension("soyshop.config.site","SOYShopConfigPageDelegateAction");
?>
