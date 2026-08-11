<?php
class CommonBreadcrumbPageUpdate extends SOYShopPageUpdate{

	function onDelete(int $pageId){
		$_dust = SOY2Logic::createInstance("module.plugins.common_breadcrumb.logic.BreadcrumbLogic")->deletePage($pageId);
		unset($_dust);
	}
}

SOYShopPlugin::extension("soyshop.page.update", "common_breadcrumb", "CommonBreadcrumbPageUpdate");
