<?php

class SOYShopMypageBase implements SOY2PluginAction{

	function getTitleFormat(){
		return null;
	}

	function getCanonicalUrl(){
		return null;
	}

	/**
	 * @param int
	 */
	function displayRegisterCompletePage(int $userId){
		//
	}
}

class SOYShopMypageDeletageAction implements SOY2PluginDelegateAction{

	private $mode;	//postがある
	private $_format;
	private $_canonical;
	private $userId;

	function run($extetensionId, $moduleId, SOY2PluginAction $action){
		switch($this->mode){
			case "title":
				$this->_format = $action->getTitleFormat();
				break;
			case "canonical":
				$this->_canonical = $action->getCanonicalUrl();
				break;
			case "register_complete":
				$action->displayRegisterCompletePage($this->userId);
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
	function setUserId(int $userId){
		$this->userId = $userId;
	}
}
SOYShopPlugin::registerExtension("soyshop.mypage", "SOYShopMypageDeletageAction");
