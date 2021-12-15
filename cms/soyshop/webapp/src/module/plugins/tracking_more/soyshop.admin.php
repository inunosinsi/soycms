<?php
class TrackingMoreAdmin extends SOYShopAdminBase{

	function execute(){
		//念の為に管理画面を開いた時に伝票番号を登録しておく
		SOY2Logic::createInstance("module.plugins.tracking_more.logic.TrackLogic")->registerSlipNumbers();
	}
}
SOYShopPlugin::extension("soyshop.admin", "tracking_more", "TrackingMoreAdmin");
