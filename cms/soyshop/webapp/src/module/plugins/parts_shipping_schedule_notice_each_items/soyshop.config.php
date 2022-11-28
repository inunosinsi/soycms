<?php
class ShippingSchuduleNoticeEachItemsConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		if(isset($_GET["item_id"])){
			SOY2::import("module.plugins.parts_shipping_schedule_notice_each_items.config.ShippingScheduleEachItemsConfigPage");
			$form = SOY2HTMLFactory::createInstance("ShippingScheduleEachItemsConfigPage");
			$form->setItemId($_GET["item_id"]);
		}else{
			SOY2::import("module.plugins.parts_shipping_schedule_notice_each_items.config.SSEIsDescriptionPage");
			$form = SOY2HTMLFactory::createInstance("SSEIsDescriptionPage");
		}

		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "商品毎出荷予定日通知プラグイン";
	}

}
SOYShopPlugin::extension("soyshop.config", "parts_shipping_schedule_notice_each_items", "ShippingSchuduleNoticeEachItemsConfig");
