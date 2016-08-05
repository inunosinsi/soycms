<?php

class ListMenuPage extends HTMLPage{

    function __construct($arg = array()){
		$this->id = $arg[0];
		HTMLPage::HTMLPage();

	
		$this->createAdd("list_page_site_link","HTMLLink", array(
			"link" => soyshop_get_page_url($arg[1]->getUri())
		));

		$this->createAdd("list_page_detail_link","HTMLLink", array(
			"link" => SOY2PageController::createLink("Site.Pages.Extra.List." . $this->id)
		));
    }
}
?>