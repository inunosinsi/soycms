<?php
class YayoiOrderCSVConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.yayoi_order_csv.config.YayoiCalendarPage");
		$form = SOY2HTMLFactory::createInstance("YayoiCalendarPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "弥生会計のCSV出力";
	}	

}
SOYShopPlugin::extension("soyshop.config", "yayoi_order_csv", "YayoiOrderCSVConfig");
?>