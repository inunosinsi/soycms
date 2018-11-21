<?php
class SOYShopOrderDetailMailBase implements SOY2PluginAction{

	//配列を返す array(array("id" => 0, "title" => ""))
	function getMailType(){}

	/**
	 * メール種別でメール文面編集画面のGETパラメータと一致すればtrueにする文字列
	 * @return string
	 */
	function activeKey(){}

	/**
	 * 注文ステータス変更時にメールの自動送信を行うか？ステータスIDを返す
	 * @return array(ステータスコード => "メール種別")
	 */
	function autoSendConfig(){}
}

class SOYShopOrderDetailMailDeletageAction implements SOY2PluginDelegateAction{

	private $_list = array();
	private $mode;

	function run($extetensionId,$moduleId,SOY2PluginAction $action){
		switch($this->mode){
			case "key":	//メールの設定時に自動送信のチェックボックスを表示するか？
				$this->_list[$moduleId] = $action->activeKey();
				break;
			case "autosend":	//ステータス変更時のメールの自動送信の設定
				$this->_list[$moduleId] = $action->autoSendConfig();
				break;
			default:
				$this->_list[$moduleId] = $action->getMailType();
		}
	}

	function getList(){
		return $this->_list;
	}

	function setMode($mode){
		$this->mode = $mode;
	}
}
SOYShopPlugin::registerExtension("soyshop.order.detail.mail",      "SOYShopOrderDetailMailDeletageAction");
