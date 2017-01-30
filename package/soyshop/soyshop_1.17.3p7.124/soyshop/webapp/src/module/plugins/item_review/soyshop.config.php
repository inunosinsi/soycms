<?php
class ItemReviewConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		include_once(dirname(__FILE__) . "/config/ItemReviewConfigFormPage.class.php");
		$form = SOY2HTMLFactory::createInstance("ItemReviewConfigFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "商品レビュープラグインの設定方法";
	}
}
SOYShopPlugin::extension("soyshop.config", "item_review", "ItemReviewConfig");
?>