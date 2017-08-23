<?php
/**
 * @class Site.Pages.SubMenu.FreePage
 * @date 2009-11-19T22:37:03+09:00
 * @author SOY2HTMLFactory
 */
class FreeMenuPage extends HTMLPage{

	var $id;

	function __construct($arg = array()){
		$this->id = $arg[0];
		parent::__construct();
		
		$this->addLink("free_page_site_link", array(
			"link" => soyshop_get_page_url($arg[1]->getUri())
		));
		
		$this->addLink("free_page_detail_link", array(
			"link" => SOY2PageController::createLink("Site.Pages.Extra.Free." . $this->id)
		));

		$dao = SOY2DAOFactory::create("site.SOYShop_PageDAO");
		$page = $dao->getById($this->id);

		$obj = $page->getPageObject();

		$this->addLabel("title", array(
			"text" => $obj->getTitle()
		));

		$this->addLabel("update_date", array(
			"text" => $obj->getUpdateDateText()
		));
	}
}


?>