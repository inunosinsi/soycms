<?php

class ReGeneratePage extends WebPage{

	var $id;

    function __construct($args) {
    	$this->id = $args[0];

		if($this->id){

			try{
				$page = SOY2DAOFactory::create("site.SOYShop_PageDAO")->getById($this->id);
				//強制再生成
				SOY2Logic::createInstance("logic.site.page.PageLogic")->generatePageDirectory($page, true);
			}catch(Exception $e){

			}
		}

		SOY2PageController::jump("Site.Pages.Detail." . $this->id . "?updated=regenerated");
    }
}
