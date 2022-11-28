<?php
class OrderTableIndexAdmin extends SOYShopAdminBase{

    function execute(){
		$cmd = "nohup php " . SOY2::RootDir() . "module/plugins/order_table_index/job/optimize.php " . SOYSHOP_ID . " > /dev/null &";
		exec($cmd);
		//SOY2Logic::createInstance("module.plugins.order_table_index.logic.OptimizeLogic")->optimize(5);
    }
}
SOYShopPlugin::extension("soyshop.admin", "order_table_index", "OrderTableIndexAdmin");
