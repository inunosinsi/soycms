<?php
class CommonTicketOrderComplete extends SOYShopOrderComplete{

	function execute(SOYShop_Order $order){
		$cart = CartLogic::getCart();
		$logic = SOY2Logic::createInstance("module.plugins.common_ticket_base.logic.TicketBaseLogic");
		$count = $logic->getTicketCount($cart, $order);

		if($count > 0){
			$logic->insertTicket($order, $count);
		}
	}
}

SOYShopPlugin::extension("soyshop.order.complete", "common_ticket_base", "CommonTicketOrderComplete");
