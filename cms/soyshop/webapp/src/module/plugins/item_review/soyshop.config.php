<?php
class ItemReviewConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.item_review.config.ItemReviewConfigFormPage");
		$form = SOY2HTMLFactory::createInstance("ItemReviewConfigFormPage");
		$form->setConfigObj($this);
		$form->execute();
		$html = $form->getObject();
		//テンプレートの中に誤りがあり、block:idの:idが消えてしまう対策
		$html = str_replace("cms=", "cms:id=", $html);
		$html = str_replace("block=", "block:id=", $html);
		$html = str_replace("使用できるcms", "使用できるcms:id", $html);
		$html = str_replace("以外のcms", "以外のcms:id", $html);
		$html = str_replace("cms一覧", "cms:id一覧", $html);
		$html = str_replace("<th>cms</th>", "<th>cms:id</th>", $html);
		return $html;
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "商品レビュープラグインの設定方法";
	}
}
SOYShopPlugin::extension("soyshop.config", "item_review", "ItemReviewConfig");
