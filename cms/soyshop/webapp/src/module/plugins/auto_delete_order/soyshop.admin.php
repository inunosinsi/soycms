<?php
class AutoDeleteOrderAdmin extends SOYShopAdminBase{

	function execute(){
		SOY2Logic::createInstance("module.plugins.auto_delete_order.logic.AutoDeleteLogic")->execute();
	}
}
SOYShopPlugin::extension("soyshop.admin", "auto_delete_order", "AutoDeleteOrderAdmin");
