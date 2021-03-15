<?php

class SOYShopMypageLoginBase implements SOY2PluginAction{

	/**
	 * ログインの認証方法を変える
	 */
	function login(){
		return true;
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

	/**
	 * 上のloginでマイページにログインしている場合のログインアカウントの顧客ID
	 * @return int
	 */
	function getUserId(){

	}
}

class SOYShopMypageLoginDeletageAction implements SOY2PluginDelegateAction{

	private $mode;	//postがある
	private $_result;
	private $_userId;

	function run($extetensionId, $moduleId, SOY2PluginAction $action){
		switch($this->mode){
			case "login":
				$this->_result = $action->login();
				break;
			case "logout":
				$action->logout();
				break;
			case "isLoggedIn":
				$action->isLoggedIn();
				break;
			case "user_id":
				if(is_null($this->_userId)) $this->_userId = $action->getUserId();
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

	function getUserId(){
		return $this->_userId;
	}
}
SOYShopPlugin::registerExtension("soyshop.mypage.login", "SOYShopMypageLoginDeletageAction");
