<?php

class FileUploadPage extends CMSWebPageBase {

	function doPost(){
		$action = SOY2ActionFactory::createInstance("SiteConfig.DetailAction");
		$result = $action->run();
		$entity = $result->getAttribute("entity");

		$res = $this->run("Entry.UploadFileAction", array("maxWidth" => $entity->getDefaultUploadResizeWidth()));
		echo json_encode($res->getAttribute("result"));
		exit;
	}
	function __construct($arg) {
		parent::__construct();

		$mode = self::getDefaultUploadMode();
		foreach(range(1,3) as $i){
			$this->addCheckBox("select_method_" . $i, array(
				"name" => "select_method",
				"selected" => ($i == $mode),
				"onclick" => "toggle_method_panel(" . $i. ")",
				"elementId" => "select_method_" . $i,
				"style" => "border-style:none; background-color:transparent;"
			));

			$this->addModel("method_" . $i, array(
				"attr:style" => ($i == $mode) ? "display:block;" : "display:none;",
				"attr:id" => "method_" . $i
			));
		}

		$this->addModel("jqueryjs", array(
			"type" => "text/JavaScript",
			"src" => SOY2PageController::createRelativeLink("./js/jquery.js")
		));

		$this->addForm("applyForm", array(
			"action"=>SOY2PageController::createLink("Entry.Editor.UploadApply")
		));

		$this->addForm("cancelForm", array(
			"action"=>SOY2PageController::createLink("Entry.Editor.UploadCancel")
		));

		$this->addForm("uploadForm");

		$this->createAdd("parameters","HTMLScript",array(
			"lang" => "text/JavaScript",
			"script" => 'var remotoURI = "'.self::getSiteUrl().ltrim(substr(self::getDefaultUpload(),1), "/").'";'
		));

		$this->addModel("file_manager_iframe", array(
			"target_src"=>SOY2PageController::createLink("FileManager.File")
		));
	}

	private function getSiteUrl(){
		$siteUrl = UserInfoUtil::getSiteURL();
		$siteId = UserInfoUtil::getSite()->getSiteId();
		if(strpos($siteUrl, "/" . $siteId . "/") === false){
			$siteUrl = rtrim($siteUrl, "/") . "/" . $siteId . "/";
		}
		return $siteUrl;
	}

	private function getDefaultUpload(){
		return self::dao()->get()->getUploadDirectory();
	}

	function getDefaultUploadMode(){
		return self::dao()->get()->getDefaultUploadMode();
	}

	private function dao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("cms.SiteConfigDAO");
		return $dao;
	}
}
