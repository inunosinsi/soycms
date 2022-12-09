<?php

class SOYShopMypageBase implements SOY2PluginAction{

	function getTitleFormat(){
		return null;
	}

	function getCanonicalUrl(){
		return null;
	}
}

class SOYShopMypageDeletageAction implements SOY2PluginDelegateAction{

	private $mode;	//postがある
	private $_format;
	private $_canonical;

	function run($extetensionId, $moduleId, SOY2PluginAction $action){
		switch($this->mode){
			case "title":
				$this->_format = $action->getTitleFormat();
				break;
			case "canonical":
				$this->_canonical = $action->getCanonicalUrl();
				break;
		}
	}

	function setMode($mode){
		$this->mode = $mode;
	}

	function getTitleFormat(){
		return $this->_format;
	}
	function getCanonicalUrl(){
		return $this->_canonical;
	}
}
SOYShopPlugin::registerExtension("soyshop.mypage", "SOYShopMypageDeletageAction");
