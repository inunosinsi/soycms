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
				$notice = $action->notice();
				if(strlen($notice)) {
					$array = array();
					$array["wording"] = $notice;
					$array["always"] = $action->always();
					$this->_contents[$moduleId] = $array;
				}
				break;
			case "error":
				$error = $action->error();
				if(strlen($error)) {
					$array = array();
					$array["wording"] = $error;
					$array["always"] = $action->always();
					$this->_contents[$moduleId] = $array;
				}
				break;
			default:
				if($action->allowDisplay()){
					$array = array();
					$array["title"] = $action->getTitle();
					$array["content"] = $action->getContent();
					$link = $action->getLink();
					if(strpos($link, "/Config/") && !AUTH_CONFIG){
						//リンクタイトルを表示させない
					}else{
						$array["link"] = $link;
						$array["link_title"] = $action->getLinkTitle();
						$array["target_blank"] = $action->getTargetBlank();
					}
					$this->_contents[$moduleId] = $array;
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
