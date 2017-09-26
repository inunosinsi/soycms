<?php

class FileUploadPage extends CMSWebPageBase {

	function doPost(){

		$res = $this->run("Entry.UploadFileAction");

		echo json_encode($res->getAttribute("result"));
		exit;
	}

	function __construct($arg) {
		parent::__construct();

		$this->createAdd("jqueryjs","HTMLModel",array(
			"type" => "text/JavaScript",
			"src" => SOY2PageController::createRelativeLink("./js/jquery.js")
		));

		$this->createAdd("applyForm","HTMLForm",array(
			"action"=>SOY2PageController::createLink("Entry.Editor.UploadApply")
		));

		$this->createAdd("cancelForm","HTMLForm",array(
			"action"=>SOY2PageController::createLink("Entry.Editor.UploadCancel")
		));

		$this->createAdd("uploadForm","HTMLForm");

		$this->createAdd("parameters","HTMLScript",array(
			"lang" => "text/JavaScript",
			"script" => 'var remotoURI = "'.UserInfoUtil::getSiteURL().substr($this->getDefaultUpload(),1).'";'.
						((defined('SOYCMS_ASP_MODE')) ? 'var siteURL = "'.UserInfoUtil::getSiteURL().'";' : 'var siteURL = "'.UserInfoUtil::getSiteURLBySiteId("").'";')
		));

		$this->createAdd("file_manager_iframe","HTMLModel",array(
			"target_src"=>SOY2PageController::createLink("FileManager.File")
		));

		$sample = "http://www.example.com/path/to/image.jpg または /path/to/picture.jpg";
		$this->createAdd("outer_link","HTMLInput",array(
			"value" => $sample,
			"style" => "width:440px;color: grey;",
			"onfocus" => "outerLinkOnFocus(this, '" . $sample . "')",
			"onblur"  => "outerLinkOnBlur(this, '" . $sample . "')",
		));

		$this->addLabel("soyCommon", array(
			"src" => SOY2PageController::createRelativeLink("./webapp/pages/files/dist/js/soycms-common.js") . "?" . SOYCMS_BUILD_TIME
		));
	}

	function getDefaultUpload(){

		$dao = SOY2DAOFactory::create("cms.SiteConfigDAO");
		$config = $dao->get();
		$dir = $config->getUploadDirectory();

		return $dir;
	}
}
