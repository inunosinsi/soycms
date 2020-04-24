<?php
class SOYShopAdminOrderChildren implements SOY2PluginAction{

	/**
	 * 検索ページのHTMLを取得
	 * @param array 商品情報
	 * @return string
	 */
	function html($items){
		return "";
	}
}

class SOYShopAdminOrderChildrenDeletageAction implements SOY2PluginDelegateAction{

	private $_html;
	private $items;

	function run($extetensionId,$moduleId,SOY2PluginAction $action){
		$this->_html = $action->html($this->items);
	}

	function getHtml(){
		return $this->_html;
	}
	function setItems($items){
		$this->items = $items;
	}
}
SOYShopPlugin::registerExtension("soyshop.admin.order.children", "SOYShopAdminOrderChildrenDeletageAction");
