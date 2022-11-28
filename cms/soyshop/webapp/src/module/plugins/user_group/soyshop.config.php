<?php
class UserGroupConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		if(isset($_GET["group_id"]) && is_numeric($_GET["group_id"])){
			SOY2::import("module.plugins.user_group.config.count.UserGroupCountPage");
			$form = SOY2HTMLFactory::createInstance("UserGroupCountPage");
		}else if(isset($_GET["import"])){
			SOY2::import("module.plugins.user_group.config.imexport.UserGroupImportPage");
			$form = SOY2HTMLFactory::createInstance("UserGroupImportPage");
		}else if(isset($_GET["export"])){
			SOY2::import("module.plugins.user_group.config.imexport.UserGroupExportPage");
			$form = SOY2HTMLFactory::createInstance("UserGroupExportPage");
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
		}else if(isset($_GET["import"])){
			return "グループ情報のCSVインポート";
		}else if(isset($_GET["export"])){
			return "グループ情報のCSVエクスポート";
		}
		return "顧客グループのカスタムサーチフィールド";
	}
}
SOYShopPlugin::extension("soyshop.config", "user_group", "UserGroupConfig");
