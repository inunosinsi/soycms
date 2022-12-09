<?php
class SupplierManagerAdminList extends SOYShopAdminListBase{

    function getTabName(){
        return "仕入先";
    }

    function getTitle(){
        return "仕入先管理";
    }

    function getContent(){
		SOY2::import("module.plugins.supplier_manager.page.SupplierManagerListPage");
        $form = SOY2HTMLFactory::createInstance("SupplierManagerListPage");
        $form->execute();
        return $form->getObject();
    }
}
SOYShopPlugin::extension("soyshop.admin.list", "supplier_manager", "SupplierManagerAdminList");
