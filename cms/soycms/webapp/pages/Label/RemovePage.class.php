<?php

class RemovePage extends CMSWebPageBase{

	function __construct($arg) {

		if(soy2_check_token()){

			$id = $arg[0];
			$action = SOY2ActionFactory::createInstance("Label.RemoveAction",array(
				"id"=>$id
			));
			$result = $action->run();
			if($result->success()){
				$this->addMessage("LABEL_REMOVE_SUCCESS");
			}else{

				$errorCode = $result->getAttribute("error_code");

				if($errorCode == RemoveAction::ERROR_BLOG_FLIPED){
					$this->addErrorMessage("LABEL_FLIP_BLOG_ERROR");
				}else{
					$this->addErrorMessage("LABEL_REMOVE_FAILED");
				}
			}
		}else{
			$this->addErrorMessage("LABEL_REMOVE_FAILED");
		}

		$this->jump("Label");

		exit;
	}
}
