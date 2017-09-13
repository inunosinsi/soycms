<?php
class UserGroupConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		if(isset($_GET["group_id"]) && is_numeric($_GET["group_id"])){
			SOY2::import("module.plugins.user_group.config.count.UserGroupCountPage");
			$form = SOY2HTMLFactory::createInstance("UserGroupCountPage");
		}else{
			SOY2::import("module.plugins.user_group.config.UserGroupCustomSearchFieldConfigPage");
			$form = SOY2HTMLFactory::createInstance("UserGroupCustomSearchFieldConfigPage");
		}

		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		if(isset($_GET["group_id"]) && is_numeric($_GET["group_id"])){
			return "グループ毎の顧客一覧";
		}
		return "顧客グループのカスタムサーチフィールド";
	}
}
SOYShopPlugin::extension("soyshop.config", "user_group", "UserGroupConfig");
