<?php

class CommonNoticeArrivalUpdate extends SOYShopItemUpdateBase{

	function addHistory(SOYShop_Item $item, int $oldStock){
		//入荷は在庫が0から1以上になった時なので、$oldStockが0でnewStockが1以上でないと実行しない
		if($oldStock === 0 && $item->getStock() > 0){

			SOY2::import("module.plugins.common_notice_arrival.util.NoticeArrivalUtil");
			$cnf = NoticeArrivalUtil::getConfig();
			if(isset($cnf["send_mail"]) && $cnf["send_mail"]){	//メールの送信設定あり
				$noticeLogic = SOY2Logic::createInstance("module.plugins.common_notice_arrival.logic.NoticeLogic");

				//入荷通知を希望している顧客のメールアドレスを取得
				$users = $noticeLogic->getUsersByItemId($item->getId(), SOYShop_NoticeArrival::NOT_SENDED, SOYShop_NoticeArrival::NOT_CHECKED);
				if(count($users) === 0) return;

				$arr = self::_mailLogic()->getUserMailConfig("arrival");
				$noticeMailLogic = SOY2Logic::createInstance("module.plugins.common_notice_arrival.logic.NoticeMailLogic");

				foreach($users as $user){
					$mailAddress = trim((string)$user->getMailAddress());
					if(!strlen($user->getMailAddress())) continue;
					$order = soyshop_get_order_object(0);
					$order->setUserId($user->getId());

					$title = $noticeMailLogic->convertMailContent(self::_getMailTitle($arr, $user), $item);
					$content = $noticeMailLogic->convertMailContent(self::_getMailContent($arr, $user), $item);
					self::_mailLogic()->sendMail($mailAddress, $title, $content, "", $order);

					//送信済みにする
					$noticeLogic->sended($item->getId(), $user->getId());
				}
			}
		}
	}

	private function _getMailTitle(array $arr, SOYShop_User $user){
		return self::_mailLogic()->convertMailContent($arr["title"], $user, new SOYShop_Order());
	}

	private function _getMailContent(array $arr, SOYShop_User $user){
		return self::_mailLogic()->convertMailContent($arr["header"] ."\n". "" . "\n" . $arr["footer"], $user, new SOYShop_Order());
	}

	private function _mailLogic(){
		static $logic;
		if(is_null($logic)) $logic = SOY2Logic::createInstance("logic.mail.MailLogic");
		return $logic;
	}
}
SOYShopPlugin::extension("soyshop.item.update", "common_notice_arrival", "CommonNoticeArrivalUpdate");
