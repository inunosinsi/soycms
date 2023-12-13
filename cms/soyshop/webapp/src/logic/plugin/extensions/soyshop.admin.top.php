<?php
class SOYShopAdminTopBase implements SOY2PluginAction{

	function notice(){
		return "";
	}

	function error(){
		return "";
	}

	function always(){
		return true;
	}

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

	/**
	 * @return boolean
	 * タイトル横に表示されるリンクURLを別タブで開くか？
	 */
	function getTargetBlank(){
		return false;
	}
}

class SOYShopAdminTopDeletageAction implements SOY2PluginDelegateAction{

	private $_contents;
	private $mode;

	function run($extetensionId, $moduleId, SOY2PluginAction $action){
		switch($this->mode){
			case "notice":
				$notice = (string)$action->notice();
				if(strlen($notice)) {
					$this->_contents[$moduleId] = array("wording" => $notice, "always" => $action->always());
				}
				break;
			case "error":
				$err = (string)$action->error();
				if(strlen($err)) {
					$this->_contents[$moduleId] = array("wording" => $err, "always" => $action->always());
				}
				break;
			default:
				if($action->allowDisplay()){
					$arr = array();
					$arr["title"] = $action->getTitle();
					$arr["content"] = $action->getContent();
					$link = (string)$action->getLink();
					if(!defined("AUTH_CONFIG_DETAIL_EXTENSION")) define("AUTH_CONFIG_DETAIL_EXTENSION", false);
					if(AUTH_CONFIG_DETAIL_EXTENSION || AUTH_CONFIG_DETAIL){ // AUTH_CONFIG_DETAIL_EXTENSIONの方は拡張ポイント(soyshop.admin.prepare)で追加を想定しています。 
						$arr["link"] = $link;
						$arr["link_title"] = $action->getLinkTitle();
						$arr["target_blank"] = $action->getTargetBlank();
					}else if(is_numeric(strpos($link, "/Config/")) && !AUTH_CONFIG){
						//リンクタイトルを表示させない
					}
					$this->_contents[$moduleId] = $arr;
				}
		}
	}

	function setMode($mode){
		$this->mode = $mode;
	}

	function getContents(){
		return $this->_contents;
	}
}
SOYShopPlugin::registerExtension("soyshop.admin.top", "SOYShopAdminTopDeletageAction");
