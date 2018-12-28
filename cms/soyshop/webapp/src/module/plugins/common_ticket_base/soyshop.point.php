<?php
class CommonTicketBase extends SOYShopPointBase{

	function doPost($userId){
		if(isset($_POST["Ticket"])){
			$newCount = mb_convert_kana($_POST["Ticket"], "a");
			if(!is_numeric($newCount)) return;

			$logic = SOY2Logic::createInstance("module.plugins.common_ticket_base.logic.TicketBaseLogic");
			$oldCount = $logic->getTicketObjByUserId($userId)->getCount();

			if($newCount != $oldCount){
				$logic->updateTicket($newCount, $userId);
			}
		}
	}
}
SOYShopPlugin::extension("soyshop.point", "common_ticket_base", "CommonTicketBase");
