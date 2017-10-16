<?php
class DownloadAssitantOrderComplete extends SOYShopOrderComplete{

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

				$commonLogic = SOY2Logic::createInstance("module.plugins.download_assistant.logic.DownloadCommonLogic");
				foreach($orderItems as $orderItem){
					$itemId = $orderItem->getItemId();
					$item = self::getItem($itemId);

					if($commonLogic->checkItemType($item)){
						$registerLogic->register($orderId, $item, $userId, $paymentStatus);
					}
				}
			}
		}
	}

	private function getItem($itemId){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		try{
			return $dao->getById($itemId);
		}catch(Exception $e){
			return new SOYShop_Item();
		}
	}
}
SOYShopPlugin::extension("soyshop.order.complete", "download_assistant", "DownloadAssitantOrderComplete");
