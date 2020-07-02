<?php
class UserGroupAdminDetail extends SOYShopAdminDetailBase{

	function getTitle(){
		return "グループ詳細";
	}

	function getContent(){
		SOY2::import("module.plugins.user_group.page.UserGroupDetailPage");
		$form = SOY2HTMLFactory::createInstance("UserGroupDetailPage");
		$form->setConfigObj($this);
		$form->setDetailId($this->getDetailId());
		$form->execute();
		return $form->getObject();
	}

	function getScripts(){
		$root = SOY2PageController::createRelativeLink("./js/");
		return array(
			//$root . "tools/soy2_date_picker.pack.js"
			$root . "tools/datepicker-ja.js",
			$root . "tools/datepicker.js"
		);
	}

	function getCSS(){
		//$root = SOY2PageController::createRelativeLink("./js/");
		return array(
			"./css/admin/user_detail.css",
			//$root . "tools/soy2_date_picker.css"
		);
	}
}
SOYShopPlugin::extension("soyshop.admin.detail", "user_group", "UserGroupAdminDetail");
