<?php
/*
 * soyshop.item.csv.php
 * Created: 2010/02/15
 */

class CommonBreadcrumbCSV extends SOYShopItemCSVBase{

	function getLabel(){
		return "パンくず";
	}

	/**
	 * export
	 * @param integer item_id
	 * @return value
	 */
	function export($itemId){
		$logic = SOY2Logic::createInstance("module.plugins.common_breadcrumb.logic.BreadcrumbLogic");
		return $logic->getListPageId($itemId);
	}

	/**
	 * import
	 * void
	 */
	function import($itemId, $value){
		$logic = SOY2Logic::createInstance("module.plugins.common_breadcrumb.logic.BreadcrumbLogic");
		$pageId = (int)$value;
		
		if(is_numeric($pageId) && $pageId > 0){
			$logic->insert($itemId, $pageId);
		}
	}
}

SOYShopPlugin::extension("soyshop.item.csv","common_breadcrumb","CommonBreadcrumbCSV");