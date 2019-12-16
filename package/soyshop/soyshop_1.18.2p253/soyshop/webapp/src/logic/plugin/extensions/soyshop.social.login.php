<?php

class SOYShopSocialLoginBase implements SOY2PluginAction{

	function buttonOnMyPageLogin(){

	}
}

class SOYShopSocialLoginDeletageAction implements SOY2PluginDelegateAction{

	private $mode;
	private $_buttons = array();

	function run($extensionId,$moduleId,SOY2PluginAction $action){
		switch($this->mode){
			case "mypage_login":
			default:
				$buttonHTML = $action->buttonOnMyPageLogin();
				if(isset($buttonHTML) && strlen($buttonHTML)){
					$this->_buttons[$moduleId] = $buttonHTML;
				}

				break;
		}
	}

	function setMode($mode){
		$this->mode = $mode;
	}

	function getButtons(){
		return $this->_buttons;
	}
}

SOYShopPlugin::registerExtension("soyshop.social.login","SOYShopSocialLoginDeletageAction");
