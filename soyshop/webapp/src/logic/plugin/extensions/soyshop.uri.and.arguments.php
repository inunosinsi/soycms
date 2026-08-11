<?php
class SOYShopUriAndArgumentsBase implements SOY2PluginAction{

	function execute(string $uri, array $args){
		return array($uri, $args);
	}
}

class SOYShopUriAndArgumentsDeletageAction implements SOY2PluginDelegateAction{

	private $uri;
	private $args;
	private $_uri;
	private $_args;

    function run($extensionId,$moduleId,SOY2PluginAction $action){
    	$v = $action->execute($this->uri, $this->args);
		if(is_array($v) && isset($v[0])) $this->_uri = $v[0];
		if(is_array($v) && isset($v[1])) $this->_args = $v[1];
    }

    function setUri(string $uri){
		$this->uri = $uri;
	}
	function setArgs(array $args){
		$this->args = $args;
	}

	function getUri(){
		return $this->_uri;
	}

	function getArgs(){
		return $this->_args;
	}
}
SOYShopPlugin::registerExtension("soyshop.uri.and.arguments","SOYShopUriAndArgumentsDeletageAction");
