<?php
class DownloadAssitantOrderComplete extends SOYShopOrderComplete{

	function execute(SOYShop_Order $order){
		if($this->isUse()){
			//ダウンロードの購入フラグ
			$downloadFlag = false;

			//支払状況
			$paymentStatus = $order->getPaymentStatus();

			$registerLogic = SOY2Logic::createInstance("module.plugins.download_assistant.logic.DownloadRegisterLogic");

			//すでに登録されている場合は処理を止める
			if($registerLogic->checkRegister($order->getId())){
				$itemOrders = soyshop_get_item_orders((int)$order->getId());

				$commonLogic = SOY2Logic::createInstance("module.plugins.download_assistant.logic.DownloadCommonLogic");
				foreach($itemOrders as $itemOrder){
					$item = soyshop_get_item_object((int)$itemOrder->getItemId());

					if($commonLogic->checkItemType($item)){
						$registerLogic->register($order->getId(), $item, $order->getUserId(), $paymentStatus);
					}
				}
			}
		}
	}
}
SOYShopPlugin::extension("soyshop.order.complete", "download_assistant", "DownloadAssitantOrderComplete");
