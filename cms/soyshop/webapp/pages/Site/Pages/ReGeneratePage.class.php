<?php

class ReGeneratePage extends WebPage{

    function __construct($args) {
		if(!isset($args[0]) || !is_numeric($args[0])) SOY2PageController::jump("Site.Pages");
    	$page = soyshop_get_page_object($args[0]);

		try{
			//強制再生成
			SOY2Logic::createInstance("logic.site.page.PageLogic")->generatePageDirectory($page, true);
		}catch(Exception $e){
			//
		}

		SOY2PageController::jump("Site.Pages.Detail." . $page->getId() . "?updated=regenerated");
    }
}
