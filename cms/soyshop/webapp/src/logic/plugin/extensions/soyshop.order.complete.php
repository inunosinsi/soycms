<?php

class SOYShopOrderComplete implements SOY2PluginAction{

	/**
	 * @return string
	 */
	function execute(SOYShop_Order $order){

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

	function run($extetensionId,$moduleId,SOY2PluginAction $action){
		if($action instanceof SOYShopOrderComplete){
			//SOYShop_Orderをセット
			$order = $this->getOrder();
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
	
	function getOrder(){
		return $this->order;
	}
	
	function setOrder($order){
		$this->order = $order;
	}
}
SOYShopPlugin::registerExtension("soyshop.order.complete","SOYShopOrderCompleteDeletageAction");
?>