<?php
class SOYShopAdminTopBase implements SOY2PluginAction{

	/**
	 * @return boolen
	 * ログインしているアカウントの権限によって表示するかどうか？
	 */
	function allowDisplay(){
		return true;
	}

	/**
	 * @return string
	 * 新着に項目を追加する際のタイトル部分
	 */
	function getTitle(){
		return "";
	}

	/**
	 * @return string
	 * 新着に項目を追加する際のコンテンツ部分
	 */
	function getContent(){
		return "";
	}

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
}

class SOYShopAdminTopDeletageAction implements SOY2PluginDelegateAction{

	private $_contents;

	function run($extetensionId, $moduleId, SOY2PluginAction $action){
		if($action->allowDisplay()){
			$array = array();
			$array["title"] = $action->getTitle();
			$array["content"] = $action->getContent();
			$array["link"] = $action->getLink();
			$array["link_title"] = $action->getLinkTitle();
			$this->_contents[$moduleId] = $array;
		}
	}

	function getContents(){
		return $this->_contents;
	}
}
SOYShopPlugin::registerExtension("soyshop.admin.top", "SOYShopAdminTopDeletageAction");
