<?php

class SOYShopMypageOrderBase implements SOY2PluginAction{

	/**
	 * @return string
	 * タイトル横に表示されるリンクのURL
	 */
	function getLink(){
		return "";
	}

	/**
	 * @return string
	 * タイトル横に表示されるリンクURLのテキスト部分
	 */
	function getLinkTitle(){
		return "";
	}

	/**
	 * @return boolean
	 * タイトル横に表示されるリンクURLを別タブで開くか？
	 */
	function getTargetBlank(){
		return false;
	}
}

class SOYShopMypageOrderDeletageAction implements SOY2PluginDelegateAction{

	private $mode;	//postがある
	private $_contents;

	function run($extetensionId, $moduleId, SOY2PluginAction $action){
		switch($this->mode){
			default:
				$arr = array();
				$arr["link"] = $action->getLink();;
				$arr["link_title"] = $action->getLinkTitle();
				$arr["target_blank"] = $action->getTargetBlank();
				$this->_contents[$moduleId] = $arr;	
		}
	}

	function setMode($mode){
		$this->mode = $mode;
	}
	function getContents(){
		return $this->_contents;
	}
}
SOYShopPlugin::registerExtension("soyshop.mypage.order", "SOYShopMypageOrderDeletageAction");
