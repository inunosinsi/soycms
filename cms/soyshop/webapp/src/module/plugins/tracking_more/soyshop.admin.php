<?php
class TrackingMoreAdmin extends SOYShopAdminBase{

	function execute(){
		//デバック用
		//SOY2Logic::createInstance("module.plugins.tracking_more.logic.TrackLogic")->searchAll();
	}
}
SOYShopPlugin::extension("soyshop.admin", "tracking_more", "TrackingMoreAdmin");
