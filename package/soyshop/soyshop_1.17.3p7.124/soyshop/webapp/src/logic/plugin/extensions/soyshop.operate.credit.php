<?php

class SOYShopOperateCreditBase implements SOY2PluginAction{

	/**
	 * @return string
	 */
	function getFormOnOrderDetailPageTitle(SOYShop_Order $order){
		
	}

	/**
	 * @return string
	 */
	function getFormOnOrderDetailPageContent(SOYShop_Order $order){

	}

	/**
	 * doPost order_detail
	 */
	function doPostOnOrderDetailPage(SOYShop_Order $order){

	}

	/**
	 * @return string
	 */
	function getFormOnUserDetailPageTitle(){
		
	}

	/**
	 * @return string
	 */
	function getFormOnUserDetailPageContent(SOYShop_User $user){

	}

	/**
	 * doPost user_detail
	 */
	function doPostOnUserDetailPage(SOYShop_User $user){

	}
}

class SOYShopOperateCreditDeletageAction implements SOY2PluginDelegateAction{
	private $mode = "order_detail";
	private $orderId;
	private $userId;
	
	private $_list;

	function run($extetensionId,$moduleId,SOY2PluginAction $action){
		switch($this->mode){
		case "user_detail":
			if(strtolower($_SERVER['REQUEST_METHOD']) == "post"){
				$action->doPostOnUserDetailPage($this->user);
			}else{
				$title = $action->getFormOnUserDetailPageTitle();
				if(strlen($title)){
					$this->_list[$moduleId]["title"] = $title;
					$this->_list[$moduleId]["content"] = $action->getFormOnUserDetailPageContent($this->user);
				}
			}
			break;
		case "order_detail":
		default:
			if(strtolower($_SERVER['REQUEST_METHOD']) == "post"){
				$action->doPostOnOrderDetailPage($this->order);
			}else{
				$title = $action->getFormOnOrderDetailPageTitle($this->order);
				if(strlen($title)){
					$this->_list[$moduleId]["title"] = $title;
					$this->_list[$moduleId]["content"] = $action->getFormOnOrderDetailPageContent($this->order);				
				}
			}
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
	function getOrder(){
		return $this->order;
	}
	function setOrder($order){
		$this->order = $order;
	}
	function getUser(){
		return $this->user;
	}
	function setUser($user){
		$this->user = $user;
	}
}

SOYShopPlugin::registerExtension("soyshop.operate.credit","SOYShopOperateCreditDeletageAction");

?>