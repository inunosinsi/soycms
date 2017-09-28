<?php

class SOYShopUserAddressBase implements SOY2PluginAction{

	function getForm($userId){}
}

class SOYShopUserAddressDeletageAction implements SOY2PluginDelegateAction{

	private $userId;
	private $_form = array();

	function run($extetensionId,$moduleId,SOY2PluginAction $action){

		$this->_form[$moduleId] = $action->getForm($this->userId);
	}

	function getForm(){
		return $this->_form;
	}

	function getUserId(){
		return $this->userId;
	}
	function setUserId($userId){
		$this->userId = $userId;
	}
}

SOYShopPlugin::registerExtension("soyshop.user.address","SOYShopUserAddressDeletageAction");
