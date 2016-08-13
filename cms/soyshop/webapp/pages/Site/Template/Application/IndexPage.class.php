<?php

class IndexPage extends WebPage{
	
	function __construct(){
		
		SOY2::import("domain.site.SOYShop_Page");
		$tempLogic = SOY2Logic::createInstance("logic.site.template.TemplateLogic");
		
		WebPage::__construct();
		
		$this->createAdd("cart_template_list", "_common.Site.Application.TemplateListComponent", array(
			"list" => $tempLogic->getApplicationTemplates(SOYShop_Page::TYPE_CART),
			"editLink" => SOY2PageController::createLink("Site.Template.Application.Editor"),
			"mode" => SOYShop_Page::TYPE_CART
		));
		
		$this->createAdd("mypage_template_list", "_common.Site.Application.TemplateListComponent", array(
			"list" => $tempLogic->getApplicationTemplates(SOYShop_Page::TYPE_MYPAGE),
			"editLink" => SOY2PageController::createLink("Site.Template.Application.Editor"),
			"mode" => SOYShop_Page::TYPE_MYPAGE
		));
	}
}
?>