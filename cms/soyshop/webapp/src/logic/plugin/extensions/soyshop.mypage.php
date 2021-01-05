<?php

class SOYShopMypageBase implements SOY2PluginAction{

	/**
	 * ログインの認証方法を変える
	 */
	function getTitleFormat(){
		return null;
	}
}

class SOYShopMypageDeletageAction implements SOY2PluginDelegateAction{

	private $mode;	//postがある
	private $_format;

	function run($extetensionId, $moduleId, SOY2PluginAction $action){
		switch($this->mode){
			case "title":
				$this->_format = $action->getTitleFormat();
				break;
		}
	}

	function setMode($mode){
		$this->mode = $mode;
	}

	function getTitleFormat(){
		return $this->_format;
	}
}
SOYShopPlugin::registerExtension("soyshop.mypage", "SOYShopMypageDeletageAction");
