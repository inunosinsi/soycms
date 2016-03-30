<?php
class DownloadAssitantOrderComplete extends SOYShopOrderComplete{

	private $dao;

	function execute(SOYShop_Order $order){
		if($this->isUse()){
			//ダウンロードの購入フラグ
			$downloadFlag = false;
			
			//支払状況
			$paymentStatus = $order->getPaymentStatus();
		
			$registerLogic = SOY2Logic::createInstance("module.plugins.download_assistant.logic.DownloadRegisterLogic");
		
			$orderId = $order->getId();
			
			//すでに登録されている場合は処理を止める
			$res = $registerLogic->checkRegister($orderId);
			if($res){
				$userId = $order->getUserId();
					
				$itemOrdersDao = SOY2DAOFactory::create("order.SOYShop_ItemOrderDAO");
				try{
					$orderItems = $itemOrdersDao->getByOrderId($orderId);
				}catch(Exception $e){
					return false;
				}
		
				foreach($orderItems as $orderItem){
					$itemId = $orderItem->getItemId();
					$item = $this->getItem($itemId);
					$itemType = $item->getType();
					
					if($itemType == SOYShop_Item::TYPE_DOWNLOAD){
						$registerLogic->register($orderId, $item, $userId, $paymentStatus);
					}
				}
			}
		}
	}
	
	function getItem($itemId){
		if(!$this->dao){
			$this->dao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		}
		
		try{
			$item = $this->dao->getById($itemId);
		}catch(Exception $e){
			$item = new SOYShop_Item();
		}
		return $item;
	}
}
SOYShopPlugin::extension("soyshop.order.complete", "download_assistant", "DownloadAssitantOrderComplete");
?>