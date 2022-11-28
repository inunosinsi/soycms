<?php

class ComplexMenuPage extends HTMLPage{

	private $id;

    function __construct($arg = array()){
		$this->id = $arg[0];
		parent::__construct();

		$this->addLink("complex_page_site_link", array(
			"link" => soyshop_get_page_url($arg[1]->getUri())
		));
		
		$this->addLink("complex_page_detail_link", array(
			"link" => SOY2PageController::createLink("Site.Pages.Extra.Complex." . $this->id)
		));
    }
}
