<?php

class RemovePage extends CMSUpdatePageBase{

	var $id;

	function doPost(){

		if(soy2_check_token() && isset($_POST["confirm"]) && $_POST["confirm"]){

			$action = SOY2ActionFactory::createInstance("Site.RemoveAction", array(
	    		"id" => $this->id,
	    		"deleteDir"      => isset($_POST["deleteDir"])      && $_POST["deleteDir"],
	    		"deleteDatabase" => isset($_POST["deleteDatabase"]) && $_POST["deleteDatabase"],
	    	));
	    	$result = $action->run();
	    	if($result->success()){
	    		$this->addMessage("REMOVE_SUCCESS");
	    		$this->jump("Site");
			}else{
				$this->addMessage("REMOVE_FAILED");
			}
		}

		$this->reload();

	}

    function __construct($args) {
    	if(!UserInfoUtil::isDefaultUser() || count($args) < 1){
    		//デフォルトユーザのみ削除可能
    		$this->jump("Site");
    		exit;
    	}

    	$this->id = (isset($args[0])) ? $args[0] : null;

    	parent::__construct();

		$SiteLogic = SOY2Logic::createInstance("logic.admin.Site.SiteLogic");
		$site = $SiteLogic->getById($this->id);

		if(!$site || $site->getSiteType() == Site::TYPE_SOY_SHOP){
			$this->jump("Site");
		}

		$this->addForm("delete_site_form");

		$this->addLabel("site_name_title", array(
			"text" => $site->getSiteName()
		));

		$this->addLabel("site_name", array(
			"text" => $site->getSiteName()
		));

		$this->addLabel("site_id", array(
			"text" => $site->getSiteId()
		));

		$this->addLink("site_url", array(
			"href" => $site->getUrl(),
			"text" => $site->getUrl(),
		));

		$this->addCheckBox("checkbox", array(
			"label" => CMSMessageManager::get("ADMIN_CONFIRM_DELETE_SITE"),
			"name"  => "confirm",
			"value" => 1,
			"style" => "margin-right:1ex;",
			"elementId" => "confirm_checkbox",
		));

		$this->addCheckBox("checkbox_delete_dir", array(
			"label" => CMSMessageManager::get("ADMIN_DELETE_SITE_DIR"),
			"name"  => "deleteDir",
			"value" => 1,
			"style" => "margin-right:1ex;"
		));

		$this->addCheckBox("checkbox_delete_db", array(
			"label" => CMSMessageManager::get("ADMIN_DELETE_SITE_DB"),
			"name"  => "deleteDatabase",
			"value" => 1,
			"style" => "margin-right:1ex;",
			"visible" => SOYCMS_DB_TYPE == "mysql"
		));


		//ルート設定中は削除できないようにする
		if($site->getIsDomainRoot()){
			$this->addMessage("ADMIN_DETACH_ROOT_SETTING_BEFORE_DELETE_SITE");
		}

		$this->addInput("button", array(
			"disabled" => $site->getIsDomainRoot(),
			"value" => CMSMessageManager::get("SOYCMS_DELETE")
		));

		$this->outputMessage();

    	HTMLHead::addLink("site.edit.css", array(
			"type" => "text/css",
			"rel" => "stylesheet",
			"href" => SOY2PageController::createRelativeLink("./css/site/edit.css") . "?" . SOYCMS_BUILD_TIME
		));
    }

    function outputMessage(){
    	$messages = CMSMessageManager::getMessages();
    	$this->addLabel("error", array(
    		"text" => implode("\n", $messages),
    		"visible" => (!empty($messages))
    	));
    }
}
?>