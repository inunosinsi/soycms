<?php

class IndexPage extends CMSWebPageBase{

	function doPost() {
    	if(soy2_check_token()){
			$action = SOY2ActionFactory::createInstance("SiteConfig.UpdateAction");
			$result = $action->run();
			if($result->success()){
				$this->addMessage("SITECONFIG_UPDATE_SUCCESS");
				$this->jump("SiteConfig");
			}else{
				$this->addErrorMessage("SITECONFIG_UPDATE_FAILED");
				$this->jump("SiteConfig");
			}
    	}else{
			$this->addErrorMessage("SITECONFIG_UPDATE_FAILED");
    	}

	}

	function __construct(){
		parent::__construct();

		$this->addForm("index_form", array(
			"action"=>SOY2PageController::createLink("SiteConfig")
		));

		$action = SOY2ActionFactory::createInstance("SiteConfig.DetailAction");
		$result = $action->run();
		$entity = $result->getAttribute("entity");

		$this->addInput("name", array(
			"value" => $entity->getName()
		));

		// SOY2HTML(soy:id="customfield")で出力
		$this->addLabel("multi_language_site_name", array(
			"html" => CMSPlugin::callCustomFieldFunctions("Site.Name")
		));
		
		$this->addTextArea("description", array(
			"text" => $entity->getDescription(),
			"name" => "description"
		));

		$this->addLabel("multi_language_site_description", array(
			"html" => CMSPlugin::callCustomFieldFunctions("Site.Description")
		));
		
		$this->addSelect("charset", array(
			"selected"=>$entity->getCharset(),
			"options"=>SiteConfig::getCharsetLists()
		));

		//hidden
		$this->addInput("url", array(
			"name" => "url",
			"value" => $entity->getConfigValue("url")
		));

		$this->createAdd("useLabelCategory","HTMLCheckBox",array(
			"name" => "useLabelCategory",
			"value" => 1,
			"type" => "checkbox",
			"selected" => $entity->useLabelCategory(),
			"label" => $this->getMessage("SOYCMS_CONFIG_USE_LABEL_CATEGORY")
		));

		$this->addSelect("uploadmode", array(
			"name" => "defaultUploadMode",
			"options" => array(1 => "ファイルをアップロードする", 2 => "既存のファイルから選ぶ", 3 => "URLを直接指定する"),
			"selected" => $entity->getDefaultUploadMode(),
			"indexOrder" => false
		));

		$this->createAdd("uploadpath","HTMLInput",array(
			"name"=>"defaultUploadDirectory",
			"value"=>$entity->getDefaultUploadDirectory(),
		));

		$this->createAdd("resizewidth","HTMLInput",array(
			"name"=>"defaultUploadResizeWidth",
			"value"=>$entity->getDefaultUploadResizeWidth(),
		));

		$this->createAdd("create_by_date","HTMLCheckBox",array(
			"name" => "createUploadDirectoryByDate",
			"value" => 1,
			"type" => "checkbox",
			"selected" => $entity->isCreateDefaultUploadDirectory(),
			"label" => $this->getMessage("SOYCMS_CONFIG_CREATE_UPLOAD_DIRECTORY_BY_DATE"),
		));

		$this->createAdd("isShowOnlyAdministrator","HTMLCheckBox",array(
			"name" => "isShowOnlyAdministrator",
			"value" => 1,
			"type" => "checkbox",
			"selected" => $entity->isShowOnlyAdministrator(),
			"label" => $this->getMessage("SOYCMS_CONFIG_SHOW_ONLY_ADMINISTRATOR"),
		));
	}

	/**
	 * サイト一覧
	 */
	private function getSiteList(){
		$SiteLogic = SOY2Logic::createInstance("logic.admin.Site.SiteLogic");
		$old = CMSUtil::switchDsn();
		$sites = $SiteLogic->getSiteList();
		CMSUtil::resetDsn($old);
		return $sites;
	}
}
