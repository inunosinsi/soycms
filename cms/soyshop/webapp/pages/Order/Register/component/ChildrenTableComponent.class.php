<?php

class ChildrenTableComponent {

	function buildTable($children){
		if(!is_array($children) || !count($children)) return "";


		SOYShopPlugin::load("soyshop.admin.order.children");
		$html = SOYShopPlugin::invoke("soyshop.admin.order.children", array("items" => $children))->getHtml();
		if(strlen($html)) return $html;

		include_once(dirname(__FILE__) . "/item/ChildrenTablePage.class.php");
        $table = SOY2HTMLFactory::createInstance("ChildrenTablePage");
		$table->setChildren($children);
		$table->execute();
        return $table->getObject();
	}
}
