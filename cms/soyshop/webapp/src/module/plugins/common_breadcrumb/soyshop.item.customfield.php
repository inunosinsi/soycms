<?php
class CommonBreadcrumbCustomField extends SOYShopItemCustomFieldBase{

	function doPost(SOYShop_Item $item){

		if(isset($_POST["breadcrumb"])){
			$pageId = $_POST["breadcrumb"];
			$itemId = $item->getId();

			$logic = SOY2Logic::createInstance("module.plugins.common_breadcrumb.logic.BreadcrumbLogic");

			$res = $logic->insert($itemId, $pageId);
		}
	}

	function getForm(SOYShop_Item $item){

		$logic = SOY2Logic::createInstance("module.plugins.common_breadcrumb.logic.BreadcrumbLogic");
		$pages = $logic->getPages();

		$pageId = $logic->getListPageId($item->getId());

		$html = array();

		$html[] = "<div class=\"form-group\">";
		$html[] = "<label for=\"breadcrumb\">商品一覧ページ用のパンくず設定</label><br>";
		$html[] = "<select name=\"breadcrumb\">";

		foreach($pages as $page){
			if($page->getId() == $pageId){
				$html[] = "<option value=\"" . $page->getId() . "\" selected=\"selected\">" . $page->getName() . "</option>";
			}else{
				$html[] = "<option value=\"" . $page->getId() . "\">" . $page->getName() . "</option>";
			}
		}

		$html[] = "</select>";
		$html[] = "</div>";

		return implode("\n", $html);
	}

	function onDelete($id){
		$logic = SOY2Logic::createInstance("module.plugins.common_breadcrumb.logic.BreadcrumbLogic");
		$res = $logic->deleteItem($id);
	}
}
SOYShopPlugin::extension("soyshop.item.customfield", "common_breadcrumb", "CommonBreadcrumbCustomField");
