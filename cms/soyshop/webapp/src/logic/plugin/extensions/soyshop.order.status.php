<?php

class SOYShopOrderStatus implements SOY2PluginAction{

	/**
	 * mailにメールタイプを指定すれば自動送信メール @ToDo mailに値を指定したら、ステータス変更時に自動送信メール
	 * @return array(ステータスコード => array(label => "ラベル", mail => "メールの種別"))
	 */
	function statusItem(){

	}

	/**
	 * mailにメールタイプを指定すれば自動送信メール @ToDo mailに値を指定したら、ステータス変更時に自動送信メール
	 * @return array(ステータスコード => array(label => "ラベル", mail => "メールの種別"))
	 */
	function paymentStatusItem(){

	}
}
class SOYShopOrderStatusDeletageAction implements SOY2PluginDelegateAction{

	private $mode = "status";
	private $_list = array();

	function run($extetensionId, $moduleId, SOY2PluginAction $action){
		if($action instanceof SOYShopOrderStatus){
			switch($this->mode){
				case "status":
					$this->_list[$moduleId] = $action->statusItem();
					break;
				case "payment":
					$this->_list[$moduleId] = $action->paymentStatusItem();
					break;
			}
		}
	}

	function getList(){
		return $this->_list;
	}

	function getMode(){
		return $this->mode;
	}
	function setMode($mode){
		$this->mode = $mode;
	}
}
SOYShopPlugin::registerExtension("soyshop.order.status", "SOYShopOrderStatusDeletageAction");
