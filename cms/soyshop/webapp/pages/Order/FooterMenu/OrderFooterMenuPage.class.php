<?php

class OrderFooterMenuPage extends HTMLPage{

	function __construct(){
		parent::__construct();

		DisplayPlugin::toggle("app_limit_function", AUTH_CSV);
		self::_buildExportModuleArea();
		self::_buildExtensionArea();
	}

	private function _buildExportModuleArea(){
		/* 出力用 */
		$list = (AUTH_CSV) ? self::_getExportModuleList() : array();

		DisplayPlugin::toggle("export_module_menu", (count($list) > 0));
		$this->createAdd("module_list", "_common.Order.ExportModuleListComponent", array(
			"list" => $list
		));

		$this->addForm("export_form", array(
			"action" => SOY2PageController::createLink("Order.Export")
		));
	}

	private function _getExportModuleList(){
		SOYShopPlugin::load("soyshop.order.export");
		return SOYShopPlugin::invoke("soyshop.order.export", array(
			"mode" => "list"
		))->getList();
	}

	private function _buildExtensionArea(){
		if(AUTH_CSV){
			SOYShopPlugin::load("soyshop.order.upload");
			$list = SOYShopPlugin::invoke("soyshop.order.upload", array(
				"mode" => "list"
			))->getList();
		}else{
			$list = array();
		}

		DisplayPlugin::toggle("upload_list", count($list));

		$this->createAdd("upload_extension_list", "_common.Order.UploadExtensionListComponent", array(
			"list" => $list
		));
	}
}
