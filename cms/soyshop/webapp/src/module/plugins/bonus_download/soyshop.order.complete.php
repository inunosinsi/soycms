<?php
SOY2::import("module.plugins.bonus_download.logic.BonusDownloadRegisterLogic");
class BonusDownloadOrderComplete extends SOYShopOrderComplete{

	function execute(SOYShop_Order $order){
		
		$logic = new BonusDownloadRegisterLogic();
		
		//すでに登録されている場合、購入特典条件に合致しない場合は処理を止める
		if($logic->checkRegister($order)){
			$logic->register($order);
		}
	}
	
}
SOYShopPlugin::extension("soyshop.order.complete", "bonus_download", "BonusDownloadOrderComplete");
?>