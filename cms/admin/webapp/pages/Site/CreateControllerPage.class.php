<?php

class CreateControllerPage extends CMSWebPageBase{

	function __construct($args) {

		if(!UserInfoUtil::isDefaultUser() || count($args) < 1){
			//デフォルトユーザのみ作成可能
			$this->jump("Site");
			exit;
		}

		$id = (isset($args[0])) ? $args[0] : null;

		if(soy2_check_token() && strlen($id)){
			try{
				$logic = SOY2Logic::createInstance("logic.admin.Site.SiteCreateLogic")->rebuild($id);
				$this->addMessage("CREATE_SUCCESS");
			}catch(Exception $e){
				$this->addMessage("CREATE_FAILED");
			}
		}else{
			$this->addMessage("CREATE_FAILED");
		}

		$this->jump("Site.Detail." . $id);

		exit;
	}
}
