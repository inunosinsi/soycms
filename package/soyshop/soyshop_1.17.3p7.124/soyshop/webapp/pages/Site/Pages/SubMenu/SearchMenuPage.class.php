<?php
/**
 * @class Site.Pages.SubMenu.FreePage
 * @date 2009-11-19T22:37:03+09:00
 * @author SOY2HTMLFactory
 */
class SearchMenuPage extends HTMLPage{

	var $id;

	function __construct($arg = array()){
		$this->id = $arg[0];
		HTMLPage::__construct();

		$this->createAdd("search_page_site_link","HTMLLink", array(
			"link" => soyshop_get_page_url($arg[1]->getUri())
		));

		$this->createAdd("search_page_detail_link","HTMLLink", array(
			"link" => SOY2PageController::createLink("Site.Pages.Extra.Search." . $this->id)
		));
	}
}


?>