<?php
//すべての商品ブロックで親商品のみ表示の設定に変更しておく
$pageDao = SOY2DAOFactory::create("site.SOYShop_PageDAO");

try{
	$pages = $pageDao->getByType(SOYShop_Page::TYPE_COMPLEX);
}catch(Exception $e){
	$pages = array();
}

if(count($pages)){
	$pageLogic = SOY2Logic::createInstance("logic.site.page.PageLogic");

	foreach($pages as $page){
		$blocks = $page->getObject()->getComplexPageBlocks();
		if(!is_string($blocks)) continue;

		$blocks = soy2_unserialize($blocks);
		if(!is_array($blocks) || !count($blocks)) continue;
		foreach($blocks as $blockId => $block){
			$params = $block->getParams();
			if(is_array($params) && count($params) > 0) continue;
			$block->setParams(array("is_parent" => 1));

			$blocks[$blockId] = $block;
		}

		$page->getObject()->setBlocks($blocks);
		$pageLogic->updatePageObject($page);
	}
}
