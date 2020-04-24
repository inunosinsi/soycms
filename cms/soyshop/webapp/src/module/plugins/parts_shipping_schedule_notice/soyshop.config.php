<?php
class ShippingSchuduleNoticeConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){

		SOY2::import("module.plugins.parts_shipping_schedule_notice.config.ShippingScheduleConfigPage");
		$form = SOY2HTMLFactory::createInstance("ShippingScheduleConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "出荷予定日通知プラグイン";
	}

}
SOYShopPlugin::extension("soyshop.config", "parts_shipping_schedule_notice", "ShippingSchuduleNoticeConfig");
