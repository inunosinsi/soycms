<?php
class CommonNoticeArrivalOrderComplete extends SOYShopOrderComplete{

	function execute(SOYShop_Order $order){
		$userId = $order->getUserId();
		$noticeLogic = SOY2Logic::createInstance("module.plugins.common_notice_arrival.logic.NoticeLogic");
		SOY2::import("module.plugins.common_notice_arrival.domain.SOYShop_NoticeArrival");
		
		$orderLogic = SOY2Logic::createInstance("logic.order.OrderLogic");
		$itemOrders = $orderLogic->getItemsByOrderId($order->getId());
		
		foreach($itemOrders as $itemOrder){
			$itemId = $itemOrder->getItemId();
			$noticeItem = $noticeLogic->getNoticeItem($itemId, $userId, SOYShop_NoticeArrival::SENDED, SOYShop_NoticeArrival::NOT_CHECKED);
			
			//IDがあった場合は処理を続ける
			if(!is_null($noticeItem->getId())){
				$noticeLogic->update($noticeItem, SOYShop_NoticeArrival::SENDED, SOYShop_NoticeArrival::CHECKED);
			}
		}
	}
}

SOYShopPlugin::extension("soyshop.order.complete", "common_notice_arrival", "CommonNoticeArrivalOrderComplete");
?>