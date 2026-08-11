<?php
class OrderMailInsertTemplateConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.order_mail_insert_template.config.InsertStringTemplateConfigPage");
		$form = SOY2HTMLFactory::createInstance("InsertStringTemplateConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 * 拡張設定に表示されたモジュールのタイトルを表示する
	 */
	function getConfigPageTitle(){
		return "商品毎のメール文面定形文テンプレートの設定";
	}
}
SOYShopPlugin::extension("soyshop.config", "order_mail_insert_template", "OrderMailInsertTemplateConfig");
