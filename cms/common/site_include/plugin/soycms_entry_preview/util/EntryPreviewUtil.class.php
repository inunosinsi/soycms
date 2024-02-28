<?php

class EntryPreviewUtil {

	const FIELD_ID = "entry_preview";

	/**
	 * 該当する記事がプレビューモードにしているか？
	 * @param int
	 * @return bool
	 */
    public static function checkPreviewMode(int $entryId){
		if(!soycms_get_entry_attribute_value($entryId, self::FIELD_ID . "_on", "bool")) return false;

		//公開している時はプレビューモードと見なさない
		$entry = soycms_get_entry_object($entryId);
		if($entry->getIsPublished() >= Entry::ENTRY_ACTIVE) return false;
		
		// @ToDo 公開期限は保留

		return true;
	}

	/**
	 * 記事投稿画面(ブログではない)で記事IDからページIDを取得する
	 * @param int
	 * @return int
	 */
	public static function getPageIdOnEntryCreatePage(int $entryId){
		try{
			$entryLabels = SOY2DAOFactory::create("cms.EntryLabelDAO")->getByEntryId($entryId);
		}catch(Exception $e){
			$entryLabels = array();
		}
		if(!count($entryLabels)) return 0;

		try{
			$list = SOY2DAOFactory::create("cms.BlogPageDAO")->getBlogPageUriListCorrespondingToBlogLabelId();
		}catch(Exception $e){
			$list = array();
		}
		if(!count($list)) return 0;

		//ページIDがあるか？
		$labelIds = array();
		foreach($entryLabels as $entryLabel){
			$labelIds[] = $entryLabel->getLabelId();
		}

		$blogUri = "";
		foreach($labelIds as $labelId){
			if(isset($list[$labelId])) {
				$blogUri = $list[$labelId][0];
			}
		}

		if(!strlen($blogUri)) return 0;
		
		try{
			return SOY2DAOFactory::create("cms.PageDAO")->getByUri($blogUri)->getId();
		}catch(Exception $e){
			return 0;
		}
	}

	/**
	 * @param int, bool
	 */
	public static function savePreviewMode(int $entryId, bool $on=false){
		$attr = soycms_get_entry_attribute_object($entryId, self::FIELD_ID . "_on");
		$v = ($on) ? 1 : "";
		$attr->setValue($v);
		soycms_save_entry_attribute_object($attr);
	}

	public static function getPreviewPostfix(int $entryId){
		return soycms_get_entry_attribute_value($entryId, self::FIELD_ID . "_postfix", "string");
	}

	/**
	 * @param int, string
	 */
	public static function savePreviewPostfix(int $entryId, string $postfix){
		$attr = soycms_get_entry_attribute_object($entryId, self::FIELD_ID . "_postfix");
		$attr->setValue(trim($postfix));
		soycms_save_entry_attribute_object($attr);
	}

	/**
	 * @param int
	 * @return string
	 */
	public static function buildPreviewPageUrl(int $pageId){
		$blog = soycms_get_blog_page_object($pageId);
		$url = UserInfoUtil::getSitePublishURL();

		$entryPageUrl = $blog->getEntryPageURL();
		if(strlen((string)$entryPageUrl)) $url .= $entryPageUrl;
		
		return $url;
	}
}