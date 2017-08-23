<?php

class BulkCreatePage extends CMSWebPageBase{

	function doPost(){

		if(soy2_check_token() && isset($_POST["captions"]) && strlen($_POST["captions"]) && strlen(trim($_POST["captions"]))){

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
			"text" => isset($_POST["captions"]) ? $_POST["captions"] : "",
			"placeholder" => UserInfoUtil::getSiteConfig("useLabelCategory") ? $this->getMessage("SOYCMS_LABEL_CREATE_PLACEHOLDER_WITH_GROUP")//ラベル名 または 分類名/ラベル名
			                                                                 : $this->getMessage("SOYCMS_LABEL_CREATE_PLACEHOLDER"),//ラベル名 または 分類名/ラベル名
		));

	}
}
