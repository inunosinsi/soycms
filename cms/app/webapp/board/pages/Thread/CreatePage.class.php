<?php

class CreatePage extends WebPage{

	function doPost(){
		$threadlogic = SOY2Logic::createInstance("logic.ThreadLogic");
		$responselogic = SOY2Logic::createInstance("logic.ResponseLogic");

		$threadId = $threadlogic->insert($_POST);
		$responselogic->insert($threadId,$_POST);

		CMSApplication::jump("Response.".$threadId);
	}

    function __construct() {
    	WebPage::__construct();
    	$this->createAdd("main_form","HTMLForm");

		$this->createAdd("back_link","HTMLLink",array(
			"link"=>SOY2PageController::createLink(APPLICATION_ID )
		));
    }
}
?>