<?php
class CommonBreadcrumbPageUpdate extends SOYShopPageUpdate{

	function onDelete($id){
		$logic = SOY2Logic::createInstance("module.plugins.common_breadcrumb.logic.BreadcrumbLogic");
		$res = $logic->deletePage($id);
	}
}

SOYShopPlugin::extension("soyshop.page.update", "common_breadcrumb", "CommonBreadcrumbPageUpdate");
