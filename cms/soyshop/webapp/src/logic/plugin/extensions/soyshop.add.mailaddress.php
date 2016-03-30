<?php
class SOYShopAddMailAddress implements SOY2PluginAction{

	/**
	 * 追加するメールアドレスの配列を取得
	 * @return array
	 */
	function getMailAddress(SOYShop_Order $order, $orderFlag){

	}
}

class SOYShopAddMailAddressDeletageAction implements SOY2PluginDelegateAction{

	private $order;
	private $orderFlag;
	private $_mailaddress;

	function run($extetensionId,$moduleId,SOY2PluginAction $action){
		
		$this->_mailaddress = $action->getMailAddress($this->order, $this->orderFlag);
	}

	function getMailAddress(){
		return $this->_mailaddress;
	}
	function getOrder(){
		return $this->order;
	}
	function setOrder($order) {
		$this->order = $order;
	}
	function setOrderFlag($orderFlag){
		$this->orderFlag = $orderFlag;
	}
}
SOYShopPlugin::registerExtension("soyshop.add.mailaddress","SOYShopAddMailAddressDeletageAction");
?>
