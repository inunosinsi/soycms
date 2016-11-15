<?php
class SOYShopOrderDetailMailBase implements SOY2PluginAction{

	//配列を返す array(array("id" => 0, "title" => ""))
	function list(){}
}

class SOYShopOrderDetailMailDeletageAction implements SOY2PluginDelegateAction{

	private $_list = array();

	function run($extetensionId,$moduleId,SOY2PluginAction $action){
		$this->_list[$moduleId] = $action->list();
	}
	
	function getList(){
		return $this->_list;
	}
}
SOYShopPlugin::registerExtension("soyshop.order.detail.mail",      "SOYShopOrderDetailMailDeletageAction");
?>
