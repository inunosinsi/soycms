<?php

class SOYShopUserFunctionBase implements SOY2PluginAction{

	/**
	 * 検索結果一覧に表示するメニューの表示文言
	 */
	function getMenuTitle(){
		return "";
	}

	/**
	 * 検索結果一覧に表示するメニューの説明
	 */
	function getMenuDescription(){
		return "";
	}
		
	/**
	 * @return html
	 */
	function getPage(){
		
	}
}

class SOYShopUserFunctionDeletageAction implements SOY2PluginDelegateAction{

	private $_list = array();
	private $mode = "list";
	private $action;

	function run($extetensionId,$moduleId,SOY2PluginAction $action){

		switch($this->mode){

			case "list":
				$this->_list[$moduleId] = array(
					"title" => $action->getMenuTitle(),
					"description" => $action->getMenuDescription()
				);
				break;

			case "execute":
			default:
				echo $action->getPage();
				break;

		}
	}

	function getList(){
		return $this->_list;
	}
	function getMode() {
		return $this->mode;
	}
	function setMode($mode) {
		$this->mode = $mode;
	}
}

SOYShopPlugin::registerExtension("soyshop.user.function","SOYShopUserFunctionDeletageAction");
?>