<?php

class IndexPage extends WebPage{
	
	function doPost(){
		
	}
	
	function __construct(){
		
		WebPage::__construct();
		
		$this->createAdd("detail_link", "HTMLLink", array(
			"link" => SOY2PageController::createLink(APPLICATION_ID . ".Sample.Detail")
		));
	}
}
?>