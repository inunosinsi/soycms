<?php

class SOYShopCommentFormBase implements SOY2PluginAction{

	/**
	 * doPost
	 */
	function doPost(SOYShop_Order $order){

	}

	/**
	 * @return string
	 */
	function getForm(SOYShop_Order $order){

	}
}
class SOYShopCommentFormDeletageAction implements SOY2PluginDelegateAction{

	private $order;
	private $_histories = array();

	function run($extetensionId, $moduleId, SOY2PluginAction $action){

		if(strtolower($_SERVER['REQUEST_METHOD']) == "post"){
			$this->_histories[$moduleId] = $action->doPost($this->order);
		}else{
			echo $action->getForm($this->order);
		}
	}
	function setOrder($order){
		$this->order = $order;
	}
	function getHistories(){
		return $this->_histories;
	}
}
SOYShopPlugin::registerExtension("soyshop.comment.form","SOYShopCommentFormDeletageAction");
