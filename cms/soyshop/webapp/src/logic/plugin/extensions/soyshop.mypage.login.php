<?php

class SOYShopMypageLoginBase implements SOY2PluginAction{

	/**
	 * ログインの認証方法を変える
	 */
	function login(){

	}

	/**
	 *
	 */
	function logout(){

	}

	/**
	 * ログインの有無の確認方法を変える
	 * @return bool
	 */
	function isLoggedIn(){

	}
}

class SOYShopMypageLoginDeletageAction implements SOY2PluginDelegateAction{

	private $mode;	//postがある
	private $_result;

	function run($extetensionId, $moduleId, SOY2PluginAction $action){
		switch($this->mode){
			case "login":
				$action->login();
				break;
			case "logout":
				$action->logout();
				break;
			case "isLoggedIn":
				$action->isLoggedIn();
				break;
			default:
				//一回でもここを通過すればtrueにする
				$this->_result = true;
		}
	}

	function setMode($mode){
		$this->mode = $mode;
	}

	function getResult(){
		return $this->_result;
	}
}
SOYShopPlugin::registerExtension("soyshop.mypage.login", "SOYShopMypageLoginDeletageAction");
