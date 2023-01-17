<?php
function soycms_read_entry_count_same_category($html, $page){

	$obj = $page->create("read_entry_count_same_category", "HTMLTemplatePage", array(
		"arguments" => array("read_entry_count_same_category", $html)
	));

	$arr = array();
	if(CMSPlugin::activeCheck("ReadEntryCount")){
		// 今開いているページがブログページであるか？
		$blogPage = soycms_get_blog_page_object($_SERVER["SOYCMS_PAGE_ID"]);
		if(is_numeric($blogPage->getId())){
			$pObj = CMSPlugin::loadPluginConfig("ReadEntryCount");
			if(!$pObj) $pObj = new ReadEntryCountPlugin();
			
			$lim = $pObj->getLimit();	
			if(!is_numeric($lim)) $lim = 15;

			$labelIds = array();
			switch(SOYCMS_BLOG_PAGE_MODE){
				case CMSBlogPage::MODE_ENTRY:
					$labels = $page->entry->getLabels();
					if(count($labels)){
						foreach($labels as $label){
								$labelIds[] = $label->getId();
						}
					}
					break;
				case CMSBlogPage::MODE_CATEGORY_ARCHIVE:
					$labelIds = $page->page->getCategoryLabelList();
					break;
				default:
			}

			SOY2::imports("site_include.plugin.read_entry_count.domain.*");
			$arr = SOY2DAOFactory::create("ReadEntryCountDAO")->getRankingByLabelIds($labelIds, $blogPage->getId(), $lim);
		}
	}
	
	SOY2::imports("site_include.plugin.read_entry_count.component.*");
	$obj->createAdd("entry_ranking_list_same_category_version", "ReadEntryRankingListComponent", array(
		"soy2prefix" => "p_block",
		"list" => $arr,
		"blogs" => (count($arr)) ? ReadEntryCountUtil::getBlogPageList() : array()
	));

	$obj->display();
}