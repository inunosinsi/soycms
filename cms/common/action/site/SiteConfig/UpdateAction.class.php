<?php

class UpdateAction extends SOY2Action{

    protected function execute(SOY2ActionRequest &$request,SOY2ActionForm &$form,SOY2ActionResponse &$response){
    	SOY2::import("domain.cms.SiteConfig");
    	if(!is_numeric($form->defaultUploadResizeWidth)) $form->defaultUploadResizeWidth = null;
    	$siteConfig = SOY2::cast("SiteConfig",$form);
    	$siteConfig->setConfigValue("url", $_POST["url"]);
    	$logic = SOY2Logic::createInstance("logic.site.SiteConfig.SiteConfigLogic");
    	try{
    		$logic->update($siteConfig);

    		$site = UserInfoUtil::getSite();
    		$site->setSiteName($siteConfig->getName());
    		UserInfoUtil::updateSite($site);


    	}catch(Exception $e){
    		return SOY2Action::FAILED;
    	}
    	return SOY2Action::SUCCESS;
    }
}

class UpdateActionForm extends SOY2ActionForm{
	var $name;
	var $description;
	var $charset;
	var $siteConfig;
	var $defaultUploadDirectory;
	var $defaultUploadResizeWidth;
	var $createUploadDirectoryByDate;
	var $isShowOnlyAdministrator;
	var $useLabelCategory;

	function setName($name) {
		$this->name = $name;
	}
	function setDescription($description) {
		$this->description = $description;
	}
	function setCharset($charset) {
		$this->charset = $charset;
	}
	function setSiteConfig($siteConfig) {
		$this->siteConfig = $siteConfig;
	}
	function setDefaultUploadDirectory($defaultUploadDirectory) {
		$this->defaultUploadDirectory = $defaultUploadDirectory;
	}

	function getCreateUploadDirectoryByDate() {
		return $this->createUploadDirectoryByDate;
	}
	function setCreateUploadDirectoryByDate($createUploadDirectoryByDate) {
		$this->createUploadDirectoryByDate = $createUploadDirectoryByDate;
	}
	
	function setDefaultUploadResizeWidth($defaultUploadResizeWidth){
		$this->defaultUploadResizeWidth = $defaultUploadResizeWidth;	
	}

	function getIsShowOnlyAdministrator() {
		return $this->isShowOnlyAdministrator;
	}
	function setIsShowOnlyAdministrator($isShowOnlyAdministrator) {
		$this->isShowOnlyAdministrator = $isShowOnlyAdministrator;
	}

	function getUseLabelCategory() {
		return $this->useLabelCategory;
	}
	function setUseLabelCategory($useLabelCategory) {
		$this->useLabelCategory = $useLabelCategory;
	}
}

?>