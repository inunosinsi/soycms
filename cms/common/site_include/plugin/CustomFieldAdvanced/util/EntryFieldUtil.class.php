<?php

class EntryFieldUtil {

	public static function getEntryObject(string $fieldValue){
		SOY2::import("domain.cms.Entry");
		if(!strlen($fieldValue) || !is_numeric(strpos($fieldValue, "-"))) return new Entry();
		$v = explode("-", $fieldValue);
		if(!isset($v[1]) || !is_numeric($v[1])) return new Entry();
	 	return SOY2Logic::createInstance("site_include.plugin.CustomField.logic.EntryFieldLogic")->getTitleAndContentByEntryId($v[1]);
	}

	public static function getLabelCaptionAndAlias(string $fieldValue){
		if(!strlen($fieldValue) || !is_numeric(strpos($fieldValue, "-"))) return array("caption" => "", "alias" => "");
		$v = explode("-", $fieldValue);
		if(!isset($v[0]) || !is_numeric($v[0])) return array("caption" => "", "alias" => "");

		$labelLogic = SOY2Logic::createInstance("logic.site.Label.LabelLogic");
		$label = $labelLogic->getById($v[0]);
		$arr = array();
		$arr["caption"] = $label->getCaption();
		$arr["alias"] = $label->getAlias();
		return $arr;
	}

	public static function getBlogTitleAndUri(string $fieldValue, string $labelCaption){
		if(!strlen($fieldValue) || !is_numeric(strpos($fieldValue, "-"))) return array("title" => "", "title" => "");
		$v = explode("-", $fieldValue);
		if(!isset($v[0]) || !is_numeric($v[0])) return array("title" => "", "uri" => "");

		$selectedLabelId = (int)$v[0];

		//ラベル名にスラッシュがある場合、親ラベルと分離する
		if(is_numeric(strpos($labelCaption, "/"))){
			$caps = explode("/", $labelCaption);
			//親カテゴリ一つの場合
			$labelCaption = $caps[1];
			$parentLabelId = SOY2Logic::createInstance("logic.site.Label.LabelLogic")->getByCaption(trim($caps[0]))->getId();
			if(is_numeric($parentLabelId)) $selectedLabelId = $parentLabelId;
			unset($parentLabelId);
		}

		return SOY2Logic::createInstance("logic.site.Page.BlogPageLogic")->getBlogPageTitleAndUriByLabelId($selectedLabelId);
	}
}
