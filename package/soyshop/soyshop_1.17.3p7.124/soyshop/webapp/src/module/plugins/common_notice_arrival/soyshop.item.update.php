<?php

class CommonNoticeArrivalUpdate extends SOYShopItemUpdateBase{

	function addHistory(SOYShop_Item $item, $oldStock){
		
		//入荷は在庫が0から1以上になった時なので、$oldStockが0でnewStockが1以上でないと実行しない
		if($oldStock === 0 && $item->getStock() > 0){
			
			$noticeLogic = SOY2Logic::createInstance("module.plugins.common_notice_arrival.logic.NoticeLogic");
			
			//入荷通知を希望している顧客のメールアドレスを取得
			$users = $noticeLogic->getUsersByItemId($item->getId(), SOYShop_NoticeArrival::NOT_SENDED, SOYShop_NoticeArrival::NOT_CHECKED);
			
			if(count($users) === 0) return;
			
			//メール文面を取得
			SOY2::import("module.plugins.common_notice_arrival.util.NoticeArrivalUtil");
			$title = NoticeArrivalUtil::getMailTitle();
			$content = NoticeArrivalUtil::getMailContent();
			
			//MailLogicの呼び出し
			$mailLogic = SOY2Logic::createInstance("logic.mail.MailLogic");
						
			foreach($users as $user){
				if(strlen($user->getMailAddress()) === 0) continue;
				$title = $noticeLogic->convertMailTitle($title, $item);
				$body = $noticeLogic->convertMailContent($content, $user, $item);
				$mailLogic->sendMail($user->getMailAddress(), $title, $body);
				
				//送信済みにする
				$noticeLogic->sended($item->getId(), $user->getId());
			}
		}
	}
	
	function display(SOYShop_Item $item){
		
	}
}
SOYShopPlugin::extension("soyshop.item.update", "common_notice_arrival", "CommonNoticeArrivalUpdate");
?>