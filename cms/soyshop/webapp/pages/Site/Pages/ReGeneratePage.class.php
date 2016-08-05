<?php

class ReGeneratePage extends WebPage{

	var $id;

    function __construct($args) {
    	$this->id = $args[0];

		if($this->id){

			$dao = SOY2DAOFactory::create("site.SOYShop_PageDAO");
			$logic = SOY2Logic::createInstance("logic.site.page.PageLogic");

			$page = $dao->getById($this->id);

			//強制再生成
			$logic->generatePageDirectory($page,true);

		}

		SOY2PageController::jump("Site.Pages.Detail." . $this->id . "?updated=regenerated");
    }
}
?>