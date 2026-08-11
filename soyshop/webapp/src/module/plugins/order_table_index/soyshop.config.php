<?php
class OrderTableIndexConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.order_table_index.config.OrderTableIndexConfigPage");
		$form = SOY2HTMLFactory::createInstance("OrderTableIndexConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "注文関連のテーブル最適化プラグイン";
	}
}
SOYShopPlugin::extension("soyshop.config", "order_table_index", "OrderTableIndexConfig");
