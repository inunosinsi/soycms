<?php

class ReDescriptionPage extends CMSWebPageBase{

	function __construct() {

		if(isset($_POST["description"]) && strlen($_POST["description"]) > 0){
			if(soy2_check_token()){
				$action = SOY2ActionFactory::createInstance("Label.ReDescriptionAction");
				$result = $action->run();

				if($result->success()){
					$this->addMessage("LABEL_MEMO_UPDATE_SUCCESS");
				}else{
					$this->addErrorMessage("LABEL_MEMO_UPDATE_FAILED");
				}
			}else{
				$this->addErrorMessage("LABEL_MEMO_UPDATE_FAILED");
			}
		}

		$this->jump("Label");
	}
}
