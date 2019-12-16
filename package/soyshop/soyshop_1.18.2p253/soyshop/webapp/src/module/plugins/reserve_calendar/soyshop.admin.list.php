<?php
class ReserveCalendarAdminList extends SOYShopAdminListBase{

    function getTabName(){
        return "予約";
    }

    function getTitle(){
        return "予約カレンダー";
    }

    function getContent(){
        SOY2::import("module.plugins.reserve_calendar.page.admin.ReserveCalendarListPage");
        $form = SOY2HTMLFactory::createInstance("ReserveCalendarListPage");
        $form->setConfigObj($this);
        $form->execute();
        return $form->getObject();
    }
}
SOYShopPlugin::extension("soyshop.admin.list", "reserve_calendar", "ReserveCalendarAdminList");
