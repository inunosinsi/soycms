<?php

class ComplexMenuPage extends HTMLPage{

    function ComplexMenuPage($arg = array()){
		$this->id = $arg[0];
		HTMLPage::HTMLPage();

		$this->createAdd("complex_page_site_link","HTMLLink", array(
			"link" => soyshop_get_page_url($arg[1]->getUri())
		));
		
		$this->createAdd("complex_page_detail_link","HTMLLink", array(
			"link" => SOY2PageController::createLink("Site.Pages.Extra.Complex." . $this->id)
		));
    }
}
?>