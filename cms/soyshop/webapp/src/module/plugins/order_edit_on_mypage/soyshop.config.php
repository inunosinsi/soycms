<?php
class OrderEditOnMyPageConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.order_edit_on_mypage.config.OrderEditOnMyPageConfigPage");
		$form = SOY2HTMLFactory::createInstance("OrderEditOnMyPageConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "マイページで注文編集プラグイン";
	}
}
SOYShopPlugin::extension("soyshop.config", "order_edit_on_mypage", "OrderEditOnMyPageConfig");
