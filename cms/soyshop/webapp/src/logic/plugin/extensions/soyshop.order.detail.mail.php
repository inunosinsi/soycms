<?php
class SOYShopOrderDetailMailBase implements SOY2PluginAction{

	/**
	 * @return array(array("id" => string, "title" => ""))
	 */
	function getMailType($mode){}

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
	private $type;	//returnとか

	function run($extetensionId,$moduleId,SOY2PluginAction $action){
		switch($this->mode){
			case "key":	//メールの設定時に自動送信のチェックボックスを表示するか？
				$key = $action->activeKey();
				if(!is_null($key)){
					$this->_list[$moduleId] = $key;
				}
				break;
			case "autosend":	//ステータス変更時のメールの自動送信の設定
				$conf = $action->autoSendConfig();
				if(!is_null($conf) && is_array($conf) && count($conf)){
					$this->_list[$moduleId] = $conf;
				}
				break;
			case "aftersend":	//注文詳細からメールを送信した時に注文状態を自動で変更する
				if($this->type == $action->activeKey()){
					$conf = $action->autoSendConfig();
					if(!is_null($conf) && is_array($conf) && count($conf)){
						$this->_list[$moduleId] = key($conf);
					}
				}
				break;
			default:
				//modeがorderかuserの場合は何かに使用するかもしれないから引数として渡しておく
				$mailType = $action->getMailType($this->mode);
				if(!is_null($mailType)){
					$this->_list[$moduleId] = $mailType;
				}
		}
	}

	function getList(){
		return $this->_list;
	}

	function setMode($mode){
		$this->mode = $mode;
	}
	function setType($type){
		$this->type = $type;
	}
}
SOYShopPlugin::registerExtension("soyshop.order.detail.mail",      "SOYShopOrderDetailMailDeletageAction");
