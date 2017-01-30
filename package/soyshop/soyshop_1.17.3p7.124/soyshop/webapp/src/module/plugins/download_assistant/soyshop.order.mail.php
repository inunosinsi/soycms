<?php
SOY2::import("module.plugins.download_assistant.common.DownloadAssistantCommon");
class DownloadAssistantMailModule extends SOYShopOrderMail{

	/**
	 * メール本文を取得
	 * @return string
	 */
	function getMailBody(SOYShop_Order $order){
		if($this->isUse()){
			$config = DownloadAssistantCommon::getConfig();
			
			$type = (isset($_GET["type"])) ? $_GET["type"] :null;
					
			//支払状況の取得
			$status = $order->getPaymentStatus();
			
			if($status == SOYShop_Order::PAYMENT_STATUS_CONFIRMED){
				$statusFlag = true;
			}elseif(isset($type) && $type == SOYShop_Order::SENDMAIL_TYPE_PAYMENT){
				$statusFlag = true;
			}else{
				$statusFlag = false;
			}
			
			//支払状況を取得して、支払確認済みならばメールを送る
			if($statusFlag && $config["allow"] == 1){
				
				$mailBody = $config["mail"];

				$assistantLogic = SOY2Logic::createInstance("module.plugins.download_assistant.logic.DownloadAssistantLogic");								
				$values = $assistantLogic->getItemIds($order->getId());
				
				//メール文面の配列を格納する
				$body = array();
														
				foreach($values as $value){
					$itemId = $value->getItemId();
					$item = $assistantLogic->getItem($itemId);
					
					$body[] = "\n" . $item->getName();
					
					$files = $assistantLogic->getDownloadFiles($order->getId(), $itemId);
					
					if(count($files) > 1){
						for($i = 0; $i < count($files); $i++){
							
							//downloadテーブルのreceived_dateに値があるかチェックする
							if(!is_null($files[$i]->getReceivedDate()) || $type == SOYShop_Order::SENDMAIL_TYPE_PAYMENT){
								$int = $i + 1;
								$body[] = $int . ". " . $files[$i]->getFileName();
								$body[] = $assistantLogic->getDownloadFilePath($files[$i]);
								if(!is_null($files[$i]->getTimeLimit())){
									$body[] = "ダウンロード期間:本日から" . date("Y年m月d日", (int)$files[$i]->getTimeLimit() - 1) . "まで";
								}
								if(!is_null($files[$i]->getCount())){
									$body[] = "ダウンロード回数:" . $files[$i]->getCount() . "回";
								}
								$body[] = "";
							}
						}
					}else{
						
						//downloadテーブルのreceived_dateに値があるかチェックする
						if(!is_null($files[0]->getReceivedDate() || $type == SOYShop_Order::SENDMAIL_TYPE_PAYMENT)){
						
							$body[] = $assistantLogic->getDownloadFilePath($files[0]);
							if(!is_null($files[0]->getTimeLimit())){
								$body[] = "ダウンロード期間:本日から" . date("Y年m月d日", $files[0]->getTimeLimit() - 1) . "まで";
							}
							if(!is_null($files[0]->getCount())){
								$body[] = "ダウンロード回数:" . $files[0]->getCount() . "回";
							}
						}
					}
				}
				
				$mailBody = str_replace("##DOWNLOAD_URL##", implode("\n", $body), $mailBody);
				$mailBody = str_replace("##DOWNLOAD_PAGE##", $this->getDownloadPageUrl(), $mailBody);
				return $mailBody;
			}
		}
	}
	
	function getDownloadPageUrl(){
		return SOYSHOP_SITE_URL . soyshop_get_mypage_uri() . "/order";
	}

	function getDisplayOrder(){
		return 10;//payment系は100番台
	}
}

SOYShopPlugin::extension("soyshop.order.mail.user", "download_assistant", "DownloadAssistantMailModule");
SOYShopPlugin::extension("soyshop.order.mail.confirm", "download_assistant", "DownloadAssistantMailModule");
SOYShopPlugin::extension("soyshop.order.mail.admin", "download_assistant", "DownloadAssistantMailModule");
?>