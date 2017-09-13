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
}
SOYShopPlugin::extension("soyshop.admin.detail", "user_group", "UserGroupAdminDetail");
