<?php
class CommonTicketBaseConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		//include_once(dirname(__FILE__) . "/config/CommonPointBaseConfigFormPage.class.php");
		SOY2::import("module.plugins.common_ticket_base.config.TicketBaseConfigPage");
		$form = SOY2HTMLFactory::createInstance("TicketBaseConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "チケットプラグインの設定";
	}
}
SOYShopPlugin::extension("soyshop.config", "common_ticket_base", "CommonTicketBaseConfig");
