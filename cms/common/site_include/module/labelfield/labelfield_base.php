<?php
function soycms_labelfield_base($html, $htmlObj, $fieldId=null){
	$obj = $htmlObj->create("soycms_labelfield_base", "HTMLTemplatePage", array(
		"arguments" => array("soycms_labelfield_base", $html)
	));

	//if(!class_exists("EntryListComponent")) SOY2::import("site_include.blog.component.EntryListComponent");
	if(!class_exists("BlockEntryListComponent")) SOY2::import("site_include.block._common.BlockEntryListComponent");
	$entries = array();

	//ブログ詳細ページのみ動作
	if(strlen($fieldId) && get_class($htmlObj) == "CMSBlogPage" && $htmlObj->mode == "_entry_"){
		preg_match('/(.*)_\d{1,2}?/', $fieldId, $tmp);	//隠し機能：複数モジュールに対応
		if(isset($tmp[1])) $fieldId = $tmp[1];

		//entryIdを取得
		$entryId = $htmlObj->entry->getId();

		$selectedLabelId = null;
		$displayCount = 0;
		if(is_numeric($entryId)){
			SOY2::import("site_include.plugin.LabelField.util.OutputLabeledEntriesUtil");
			$selectedLabelId = OutputLabeledEntriesUtil::getSelectedLabelId($entryId, $fieldId);
			if(is_numeric($selectedLabelId) && $selectedLabelId > 0) $displayCount = OutputLabeledEntriesUtil::getDisplayCount($entryId, $fieldId);
		}

		if($displayCount > 0){
			$sort = OutputLabeledEntriesUtil::getDisplaySort($entryId, $fieldId);	// 0:asc, 1:desc
			$logic = SOY2Logic::createInstance("logic.site.Entry.EntryLogic", array("limit" => $displayCount, "reverse" => ($sort == 1)));
			$entries = $logic->getOpenEntryByLabelId($selectedLabelId);
			unset($logic);
		}
	}

	$obj->createAdd("entry_list", "BlockEntryListComponent", array(
		"soy2prefix" => "l_block",
		"list" => $entries
	));


	$obj->display();
}
