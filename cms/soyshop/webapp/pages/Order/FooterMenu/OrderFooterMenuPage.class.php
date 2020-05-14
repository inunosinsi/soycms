<?php

class OrderFooterMenuPage extends HTMLPage{

	function __construct(){
		parent::__construct();

		self::_buildExportModuleArea();
		self::_buildExtensionArea();
	}

	private function _buildExportModuleArea(){
		/* 出力用 */
		$this->createAdd("module_list", "_common.Order.ExportModuleListComponent", array(
			"list" => self::_getExportModuleList()
		));

		$this->addForm("export_form", array(
			"action" => SOY2PageController::createLink("Order.Export")
		));
	}

	private function _getExportModuleList(){
		SOYShopPlugin::load("soyshop.order.export");
		$list = SOYShopPlugin::invoke("soyshop.order.export", array(
			"mode" => "list"
		))->getList();

		DisplayPlugin::toggle("export_module_menu", (count($list) > 0));

		return $list;
	}

	private function _buildExtensionArea(){
		SOYShopPlugin::load("soyshop.order.upload");
		$list = SOYShopPlugin::invoke("soyshop.order.upload", array(
			"mode" => "list"
		))->getList();

		DisplayPlugin::toggle("upload_list", count($list));

		$this->createAdd("upload_extension_list", "_common.Order.UploadExtensionListComponent", array(
			"list" => $list
		));
	}
}
