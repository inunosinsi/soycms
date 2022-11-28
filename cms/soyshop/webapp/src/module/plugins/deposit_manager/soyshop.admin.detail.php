<?php
class DepositManagerAdminDetail extends SOYShopAdminDetailBase{

	function getTitle(){
		return "入金詳細";
	}

	function getContent(){
		SOY2::import("module.plugins.deposit_manager.page.DepositManagerDetailPage");
        $form = SOY2HTMLFactory::createInstance("DepositManagerDetailPage");
		$form->setDetailId($this->getDetailId());
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
SOYShopPlugin::extension("soyshop.admin.detail", "deposit_manager", "DepositManagerAdminDetail");
