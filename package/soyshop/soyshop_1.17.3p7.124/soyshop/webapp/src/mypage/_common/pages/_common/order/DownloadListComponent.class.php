<?php

class DownloadListComponent extends HTMLList{
	
	private $order;
	
	protected function populateItem($entity){
		
		$paymentFlag = ((int)$this->order->getPaymentStatus() === SOYShop_Order::PAYMENT_STATUS_CONFIRMED);
		
		$this->addLabel("file_name", array(
			"text" => $entity->getFileName()
		));
		
		$this->addLabel("download_order_date", array(
			"text" => date("Y年m月d日", $entity->getOrderDate())
		));
		
		$this->addLabel("time_limit", array(
			"text" => (!is_null($entity->getTimeLimit())) ? date("Y年m月d日", $entity->getTimeLimit() - 1) : MessageManager::get("FREEUNLIMITED")
		));
		
		$this->addLabel("count", array(
			"text" => (!is_null($entity->getCount())) ? MessageManager::get("COUNT", array("count" => $entity->getCount())) : MessageManager::get("FREEUNLIMITED")
		));
		
		$this->addModel("is_download", array(
			"visible" => ($paymentFlag)
		));
		
		$this->addLink("download_link", array(
			"link" => SOYSHOP_SITE_URL . soyshop_get_mypage_uri() . "?soyshop_download=download_assistant&token=" . $entity->getToken(),
		));
		
		$this->addModel("no_download", array(
			"visible" => (!$paymentFlag)
		));
		
		$this->addLabel("no_download_message", array(
			"text" => $this->getNoDownloadMessage($paymentFlag,$entity)
		));
	}
	
	function getNoDownloadMessage($paymentFlag, $entity){
		
		$message = "";
		
		//支払期限を見る
		if(!$paymentFlag){
			$message = MessageManager::get("WAITING_FOR_PAYMENT");
		}else{
			
			//ダウンロード可能期限が過ぎた場合
			if(!is_null($entity->getTimeLimit()) && $entity->getTimeLimit() < time()){
				$message = MessageManager::get("OVER_DOWNLOAD_TERM");
			}
			
			if(!is_null($entity->getCount()) && $entity->getCount() === 0){
				$message = MessageManager::get("OVER_DOWNLOAD_COUNT");
			}
		}
		return $message;
	}
	
	function setOrder($order){
		$this->order = $order;
	}
}
?>