<?php
function soycms_read_entry_count_by_blog_page_id($html, $page){

	$obj = $page->create("read_entry_count_by_blog_page_id", "HTMLTemplatePage", array(
		"arguments" => array("read_entry_count_by_blog_page_id", $html)
	));

	$arr = array();	
	$blogPageId = 0;
	if(CMSPlugin::activeCheck("ReadEntryCount")){

		$lines = explode("\n", $html);
		foreach($lines as $line){
			if(is_bool(strpos($line, "p_block:id=\"entry_ranking_list_blog_id_version\""))) continue;
			preg_match('/<!--.*cms:blog=\"([\d]*?)\".*-->/', $line, $tmp);
			if(isset($tmp[1])){
				$blogPageId = (int)$tmp[1];
				break;
			}
		}
		
		if($blogPageId > 0){
			$pObj = CMSPlugin::loadPluginConfig("ReadEntryCount");
			if(!$pObj) $pObj = new ReadEntryCountPlugin();
		
			$lim = $pObj->getLimit();	
			if(!is_numeric($lim)) $lim = 15;
			
			SOY2::imports("site_include.plugin.read_entry_count.domain.*");
			$arr = SOY2DAOFactory::create("ReadEntryCountDAO")->getRankingByBlogPageId((int)soycms_get_blog_page_object($blogPageId)->getBlogLabelId(), $lim);
		}
	}

	SOY2::imports("site_include.plugin.read_entry_count.component.*");
	$obj->createAdd("entry_ranking_list_blog_id_version", "ReadEntryRankingListComponent", array(
		"soy2prefix" => "p_block",
		"list" => $arr,
		"blogs" => ($blogPageId > 0) ? ReadEntryCountUtil::getBlogPageList() : array()
	));

	$obj->display();
}