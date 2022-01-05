<?php
class ItemReviewCustomField extends SOYShopItemCustomFieldBase{

    function doPost(SOYShop_Item $item){}

    function onOutput($htmlObj, SOYShop_Item $item){
		SOY2::import("module.plugins.item_review.util.ItemReviewUtil");
		$average = self::getEvaluationAverageByItemId($item->getId());

		//評価があれば表示する
		$htmlObj->addModel("is_evaluation_average", array(
			"visible" => ($average > 0),
			"soy2prefix" => SOYSHOP_SITE_PREFIX
		));

		//数字形式
		$htmlObj->addLabel("evaluation_average", array(
			"text" => $average,
			"soy2prefix" => SOYSHOP_SITE_PREFIX
		));

		//星形式
		$htmlObj->addLabel("evaluation_average_star", array(
			"html" => ItemReviewUtil::buildEvaluationString($average),
			"soy2prefix" => SOYSHOP_SITE_PREFIX
		));
    }

	private function getEvaluationAverageByItemId($itemId){
		static $dao;
		if(is_null($dao)) {
			SOY2::import("module.plugins.item_review.domain.SOYShop_ItemReviewDAO");
			$dao = SOY2DAOFactory::create("SOYShop_ItemReviewDAO");
		}

		if(!isset($itemId) || !is_numeric($itemId) || (int)$itemId === 0) return 0;

		return $dao->getEvaluationAverageByItemId($itemId);
	}

    function getForm(SOYShop_Item $item){}

    function onDelete(int $itemId){}
}
SOYShopPlugin::extension("soyshop.item.customfield", "item_review", "ItemReviewCustomField");
