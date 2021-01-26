<?php
class SOYShopAuthBase implements SOY2PluginAction{

	private $classPath;

	function auth(){}

	function getClassPath(){
		return $this->classPath;
	}
	function setClassPath($classPath){
		$this->classPath = $classPath;
	}
}

class SOYShopAuthDeletageAction implements SOY2PluginDelegateAction{

	private $classPath;

	function run($extetensionId, $moduleId, SOY2PluginAction $action){
		$action->setClassPath($this->classPath);
		$action->auth();
	}

	function setClassPath($classPath){
		$this->classPath = $classPath;
	}
}
SOYShopPlugin::registerExtension("soyshop.auth", "SOYShopAuthDeletageAction");
