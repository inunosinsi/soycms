<?php

class SOYShopMypageCard implements SOY2PluginAction{

	/**
	 * @return boolean
	 * クレジットカードのカード番号の入力ページ等の追加ページを持っているか？
	 */
	function hasOptionPage(){
		return false;
	}

	/**
	 * @return string
	 * hasOptionPageがtrueの場合、注文完了後の追加ページの表示内容
	 */
	function getOptionPage(){
		return "";
	}

	/**
	 * @return bool
	 * 追加ページでPOSTを送信した後に読み込まれる
	 */
	function onPostOptionPage(){
		return true;
	}
}

class SOYShopMypageCardDeletageAction implements SOY2PluginDelegateAction{

	private $mode;	//postがある
	private $moduleId;
	private $_result;
	private $isOpts = array();

	function run($extetensionId, $moduleId, SOY2PluginAction $action){
		switch($this->mode){
			case "post":
				$this->_result = $action->onPostOptionPage();
				break;
			case "form":
				echo $action->getOptionPage();
				break;
			default:
				$res = $action->hasOptionPage();
				if(is_bool($res) && $res) $this->isOpts[$moduleId] = $res;
		}
	}

	function hasOptionPage(){
		return (is_array($this->isOpts) && count($this->isOpts));
	}

	function getResult(){
		return $this->_result;
	}

	function setMode($mode){
		$this->mode = $mode;
	}
	function setModuleId($moduleId){
		$this->moduleId = $moduleId;
	}
}
SOYShopPlugin::registerExtension("soyshop.mypage.card", "SOYShopMypageCardDeletageAction");
