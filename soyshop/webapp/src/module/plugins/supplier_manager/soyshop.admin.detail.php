<?php
class SupplierManagerAdminDetail extends SOYShopAdminDetailBase{

	function getTitle(){
		return "仕入先";
	}

	function getContent(){
		SOY2::import("module.plugins.supplier_manager.page.SupplierManagerDetailPage");
        $form = SOY2HTMLFactory::createInstance("SupplierManagerDetailPage");
		$form->setDetailId($this->getDetailId());
        $form->execute();
        return $form->getObject();
	}

}
SOYShopPlugin::extension("soyshop.admin.detail", "supplier_manager", "SupplierManagerAdminDetail");
