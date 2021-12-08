<?php
class CommonNoticeArrivalOrderComplete extends SOYShopOrderComplete{

	function execute(SOYShop_Order $order){
		$noticeLogic = SOY2Logic::createInstance("module.plugins.common_notice_arrival.logic.NoticeLogic");
		SOY2::import("module.plugins.common_notice_arrival.domain.SOYShop_NoticeArrival");

		$itemOrders = SOY2Logic::createInstance("logic.order.OrderLogic")->getItemsByOrderId($order->getId());
		foreach($itemOrders as $itemOrder){
			$noticeItem = $noticeLogic->getNoticeItem((int)$itemOrder->getItemId(), (int)$order->getUserId(), SOYShop_NoticeArrival::SENDED, SOYShop_NoticeArrival::NOT_CHECKED);

			//IDがあった場合は処理を続ける
			if(is_numeric($noticeItem->getId())){
				$noticeLogic->update($noticeItem, SOYShop_NoticeArrival::SENDED, SOYShop_NoticeArrival::CHECKED);
			}
		}
	}
}

SOYShopPlugin::extension("soyshop.order.complete", "common_notice_arrival", "CommonNoticeArrivalOrderComplete");
