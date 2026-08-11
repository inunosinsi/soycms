<?php
class DepositManagerAdminList extends SOYShopAdminListBase{

    function getTabName(){
        return "入金";
    }

    function getTitle(){
        return "入金管理";
    }

    function getContent(){
		SOY2::import("module.plugins.deposit_manager.page.DepositManagerListPage");
        $form = SOY2HTMLFactory::createInstance("DepositManagerListPage");
        $form->execute();
        return $form->getObject();
    }

	// function getCSS(){
	// 	$root = SOY2PageController::createRelativeLink("./js/");
	// 	return array(
	// 		$root . "tools/soy2_date_picker.css"
	// 	);
	// }

	function getScripts(){
		$root = SOY2PageController::createRelativeLink("./js/");
		return array(
			//$root . "tools/soy2_date_picker.pack.js"
			$root . "tools/datepicker-ja.js",
			$root . "tools/datepicker.js"
		);
	}
}
SOYShopPlugin::extension("soyshop.admin.list", "deposit_manager", "DepositManagerAdminList");
