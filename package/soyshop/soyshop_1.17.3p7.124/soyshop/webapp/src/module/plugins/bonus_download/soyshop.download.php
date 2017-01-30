<?php
SOY2::import("module.plugins.bonus_download.logic.BonusDownloadDownloadLogic");
class BonusDownloadDownload extends SOYShopDownload{

	function execute(){
		
		//トラッキングナンバー 
		if(!isset($_GET["tn"]) && !isset($_GET["t"])){
			exit;
		}
		
		$trackingNumber = $_GET["tn"];//注文のトラッキングナンバー
		$token = $_GET["t"];//トークン
		
		$order = BonusDownloadConfigUtil::getBonusOrder(null, $trackingNumber);
		
		//支払状況を確認する
		if($order->getPaymentStatus() == SOYShop_Order::PAYMENT_STATUS_CONFIRMED){
			$filename = $this->getDownload($order, $token);
			if($filename && strlen($filename) > 0){
				$logic = new BonusDownloadDownloadLogic();
				$logic->downloadFile($filename);
			}
		}	
		
		echo "ダウンロードに失敗しました。";
		exit;
	}
	
	/**
	 * @param SOYShop_Order $order
	 * @param integer $token トークン
	 * @return string ファイル名 || boolean false
	 */
	public function getDownload(SOYShop_Order $order, $token){
		
		$list = BonusDownloadConfigUtil::getListOrderAttribute($order, "bonus_download.list");
		if(is_array($list) && in_array($token, $list)){
			//$download["filename"] ファイル名
			//$download["timelimit"] ダウンロード有効期限。ない場合はnull
			$filename = BonusDownloadConfigUtil::getOrderAttribute($order, "bonus_download.filename.". $token);
			$timelimit = BonusDownloadConfigUtil::getOrderAttribute($order, "bonus_download.timelimit.". $token);
				
			if(!is_null($filename)){
				
				//有効期限が設定されていて、過ぎていた場合
				if(!is_null($timelimit) && is_numeric($timelimit) && $timelimit < time()){
					echo "ダウンロード有効期限が過ぎています";
					exit;
				}
				
				return $filename;
			}	
			
			return false;
		}else{
			return false;
		}
		
	}
	
}
SOYShopPlugin::extension("soyshop.download", "bonus_download", "BonusDownloadDownload");
?>