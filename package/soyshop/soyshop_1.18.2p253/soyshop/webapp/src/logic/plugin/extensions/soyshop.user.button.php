<?php

class SOYShopUserButtonBase implements SOY2PluginAction{

	function buttonOnTitle($userId){

	}
}

class SOYShopUserButtonDeletageAction implements SOY2PluginDelegateAction{

	private $userId;
	private $_buttons = array();

	function run($extensionId,$moduleId,SOY2PluginAction $action){
		$this->_buttons[$moduleId] = $action->buttonOnTitle($this->userId);
	}

	function getButtons(){
		return $this->_buttons;
	}

	function getUserId(){
		return $this->userId;
	}
	function setUserId($userId){
		$this->userId = $userId;
	}
}

SOYShopPlugin::registerExtension("soyshop.user.button","SOYShopUserButtonDeletageAction");
