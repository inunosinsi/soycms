<?php
class AutoDeleteOrderAdmin extends SOYShopAdminBase{

	function execute(){
		$cmd = "nohup php " . SOY2::RootDir() . "module/plugins/auto_delete_order/job/delete.php " . SOYSHOP_ID . " > /dev/null &";
		exec($cmd);
		//SOY2Logic::createInstance("module.plugins.auto_delete_order.logic.AutoDeleteLogic")->execute();
	}
}
SOYShopPlugin::extension("soyshop.admin", "auto_delete_order", "AutoDeleteOrderAdmin");
