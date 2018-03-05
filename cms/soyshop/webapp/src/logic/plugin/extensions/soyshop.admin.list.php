<?php
class SOYShopAdminListBase implements SOY2PluginAction{

	function getTabName(){}
	function getTitle(){}
	function getContent(){}
	function getScripts(){}
	function getCSS(){}
}

class SOYShopAdminListDeletageAction implements SOY2PluginDelegateAction{

	private $_contents = array();
	private $_scripts;
	private $_css;
	private $mode;
	private $pluginId;	//同一ページ内で一度loadしてしまっているため、二回目の読み込みでpluginIDの指定が出来ない対策

	function run($extetensionId, $moduleId, SOY2PluginAction $action){
		$array = array();
		switch($this->mode){
			case "tab":
				$array["tab"] = $action->getTabName();
				break;
			case "list":
				if(self::getReadedPlugin() == $this->pluginId && $moduleId == $this->pluginId){
					$array["title"] = $action->getTitle();
					$array["content"] = $action->getContent();

					//上書き防止
					if(is_null($this->_scripts)) $this->_scripts = $action->getScripts();
					if(is_null($this->_css)) $this->_css = $action->getCSS();
				}
				break;
			default:
				$array["title"] = $action->getTitle();
		}
		$this->_contents[$moduleId] = $array;
	}

	private function getReadedPlugin(){
		static $pluginId;
		if(is_null($pluginId)){
			$uri = $_SERVER["REQUEST_URI"];
			$uri = trim(substr($_SERVER["REQUEST_URI"], strpos($_SERVER["REQUEST_URI"], "/Extension/") + 10), "/") . "/";
			if(strpos($uri, "/")) $uri = substr($uri, 0, strpos($uri, "/"));
			if(strpos($uri, "?")) $uri = substr($uri, 0, strpos($uri, "?"));
			if(strlen($uri)) $pluginId = $uri;
		}

		return $pluginId;
	}

	function getContents(){
		return $this->_contents;
	}
	function getScripts(){
		return $this->_scripts;
	}
	function getCSS(){
		return $this->_css;
	}

	function setMode($mode){
		$this->mode = $mode;
	}
	function setPluginId($pluginId){
		$this->pluginId = $pluginId;
	}
}
SOYShopPlugin::registerExtension("soyshop.admin.list", "SOYShopAdminListDeletageAction");
