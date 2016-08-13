<?php

class BulkCreatePage extends CMSWebPageBase{

	function doPost(){
		
    	if(soy2_check_token()){

			define("SOY2ACTION_AUTO_GENERATE",true);
			
			$action = SOY2ActionFactory::createInstance("Label.LabelBulkCreateAction");
			$result = $action->run();
			
			if($result->success()){
				$this->addMessage("LABEL_CREATE_SUCCESS");
				$this->jump("Label");
			}else{
				$this->addErrorMessage("LABEL_CREATE_FAILED");
				//CMSMessageManager::addErrorMessage($result->getErrorMessage());
			}
    	}
		
	}
	
    function __construct() {
    	WebPage::__construct();
    	$this->createAdd("bulk_create_label","HTMLForm");

		$this->createAdd("bulk_create_label_captions", "HTMLTextArea", array(
			"name" => "captions",
			"text" => @$_POST["captions"]
		));

    }
}
