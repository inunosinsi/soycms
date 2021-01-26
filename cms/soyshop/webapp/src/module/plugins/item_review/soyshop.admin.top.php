<?php
class ItemReviewAdminTop extends SOYShopAdminTopBase{

	function getLink(){
		return SOY2PageController::createLink("Review");
	}

	function getLinkTitle(){
		return "レビュー一覧";
	}

	function getTitle(){
		return "新着のレビュー";
	}

	function getContent(){
		SOY2::import("module.plugins.item_review.page.ItemReviewAreaPage");
		$form = SOY2HTMLFactory::createInstance("ItemReviewAreaPage");
		$form->execute();
		return $form->getObject();
	}

	function allowDisplay(){
		return AUTH_REVIEW;
	}
}
SOYShopPlugin::extension("soyshop.admin.top", "item_review", "ItemReviewAdminTop");
