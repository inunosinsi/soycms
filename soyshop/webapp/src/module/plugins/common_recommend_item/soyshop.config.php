<?php
class CommonRecommendItemConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){

		SOY2::import("module.plugins.common_recommend_item.config.RecommendItemConfigPage");
		$form = SOY2HTMLFactory::createInstance("RecommendItemConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "おすすめ商品";
	}
	
}
SOYShopPlugin::extension("soyshop.config", "common_recommend_item", "CommonRecommendItemConfig");
?>