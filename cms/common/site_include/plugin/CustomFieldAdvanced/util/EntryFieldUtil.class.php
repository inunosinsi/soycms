<?php

class EntryFieldUtil {

	/**
	 * @param string
	 * @return array(siteId, labelId, entryId)
 	 */
	public static function divideIds(string $fieldValue){
		if(!strlen($fieldValue) || !is_numeric(strpos($fieldValue, "-"))) return array(CMSUtil::getCurrentSiteId(), 0, 0);
		$v = explode("-", $fieldValue);
		$cnt = count($v);
		if($cnt < 2) return array(CMSUtil::getCurrentSiteId(), 0, 0);
		return ($cnt == 2) ? array(CMSUtil::getCurrentSiteId(), (int)$v[0], (int)$v[1]) : array((int)$v[0], (int)$v[1], (int)$v[2]);
	}

	/**
	 * @param int entryId, int siteId(現在のサイトと異なる時)
	 * @return Entry
	 */
	public static function getEntryObjectById(int $entryId){
		SOY2::import("domain.cms.Entry");
		if($entryId === 0) return new Entry();
		$entry = SOY2Logic::createInstance("site_include.plugin.CustomField.logic.EntryFieldLogic")->getTitleAndContentByEntryId($entryId);
		return $entry;
	}

	/**
	 * @param int labelId
	 * @return array(caption, alias)
	 */
	public static function getLabelCaptionAndAliasById(int $labelId){
		if($labelId === 0) return array("caption" => "", "alias" => "");

		$label = SOY2Logic::createInstance("logic.site.Label.LabelLogic")->getById($labelId);
		$arr = array();
		$arr["caption"] = $label->getCaption();
		$arr["alias"] = $label->getAlias();
		return $arr;
	}

	public static function getBlogTitleAndUri(int $labelId, string $labelCaption){
		if($labelId === 0) return array("title" => "", "uri" => "");

		//ラベル名にスラッシュがある場合、親ラベルと分離する
		if(is_numeric(strpos($labelCaption, "/"))){
			$caps = explode("/", $labelCaption);
			//親カテゴリ一つの場合
			$labelCaption = $caps[1];
			$parentLabelId = SOY2Logic::createInstance("logic.site.Label.LabelLogic")->getByCaption(trim($caps[0]))->getId();

			if(is_numeric($parentLabelId)) $labelId = $parentLabelId;
			unset($parentLabelId);
		}

		return SOY2Logic::createInstance("logic.site.Page.BlogPageLogic")->getBlogPageTitleAndUriByLabelId($labelId);
	}
}
