<?php
/**
 * 使われていない
 */
class RenamePage extends CMSWebPageBase{

	function __construct() {
		//使われていないようだが念のためふさいでおく
		if(soy2_check_token()){
			$action = SOY2ActionFactory::createInstance("Label.RenameAction");
			$result = $action->run();

			if($result->success()){
				$this->addMessage("LABEL_RENAME_SUCCESS");
			}else{
				$this->addErrorMessage("LABEL_RENAME_FAILED");
			}
		}else{
			$this->addErrorMessage("LABEL_RENAME_FAILED");
		}
		$this->jump("Label");
	}
}
