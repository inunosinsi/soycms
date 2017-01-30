<?php

class SOYShopOrderComplete implements SOY2PluginAction{

	/**
	 * @return string
	 */
	function execute(SOYShop_Order $order){

	}
	
	/**
	 * @return tracking_number
	 */
	function getTrackingNumber(SOYShop_Order $order){
		
	}
	
	private $isUse = false;

	function setIsUse($flag){
		$this->isUse = (boolean)$flag;
	}

	function isUse(){
		return $this->isUse;
	}

}
class SOYShopOrderCompleteDeletageAction implements SOY2PluginDelegateAction{

	private $order;
	private $mode;
	
	private $_trackingNumber;

	function run($extetensionId,$moduleId,SOY2PluginAction $action){
		if($action instanceof SOYShopOrderComplete){
			//SOYShop_Orderをセット
			$order = $this->getOrder();
			
			switch($this->mode){
				case "tracking_number":
					$this->_trackingNumber = $action->getTrackingNumber($order);	//拡張ポイントの重複はなし
					break;
				default:
					//注文時に選択されていればisUseフラグを立てる
					if($order){
						$moduleList = $order->getModuleList();
						if(isset($moduleList[$moduleId])){
							$action->setIsUse(true);
						}
					}
					
					$action->execute($this->order);
			}	
		}
	}
	
	function getTrackingNumber(){
		return $this->_trackingNumber;
	}
	
	function getOrder(){
		return $this->order;
	}
	
	function setOrder($order){
		$this->order = $order;
	}
	
	function setMode($mode){
		$this->mode = $mode;
	}
}
SOYShopPlugin::registerExtension("soyshop.order.complete","SOYShopOrderCompleteDeletageAction");
?>