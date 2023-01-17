<?php
function soycms_read_entry_count($html, $page){

	$obj = $page->create("read_entry_count", "HTMLTemplatePage", array(
		"arguments" => array("read_entry_count", $html)
	));

	$arr = array();
	if(CMSPlugin::activeCheck("ReadEntryCount")){
		$pObj = CMSPlugin::loadPluginConfig("ReadEntryCount");
		if(!$pObj) $pObj = new ReadEntryCountPlugin();
		
		$lim = $pObj->getLimit();	
		if(!is_numeric($lim)) $lim = 15;

		SOY2::imports("site_include.plugin.read_entry_count.domain.*");
		$arr = SOY2DAOFactory::create("ReadEntryCountDAO")->getRanking($lim);
	}

	SOY2::imports("site_include.plugin.read_entry_count.component.*");
	$obj->createAdd("entry_ranking_list_module_version", "ReadEntryRankingListComponent", array(
		"soy2prefix" => "p_block",
		"list" => $arr,
		"blogs" => (count($arr)) ? ReadEntryCountUtil::getBlogPageList() : array()
	));

	$obj->display();
}