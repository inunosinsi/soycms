<?php

class DragAndDropPage extends CMSWebPageBase{
	
	var $id;
	
	function doPost(){
		SOY2::import("util.CMSFileManager");
		
		$root = str_replace("\\",'/',realpath(UserInfoUtil::getSiteDirectory()));
		
		$id = $this->id;
		
		$upload = @$_FILES['file'];
		
		if($id && $upload){
			$result = CMSFileManager::upload($root,$id,$upload);
		}
		
		exit;
	}
	
	function __construct($args){
		
		$this->id = @$args[0];
		
		parent::__construct();
		
		$script = array();
		
		$script[] = "var applet_code = 'org.oklab.upload.ClientUI.class';";
		$script[] = "var archive_url = '".SOY2PageController::createRelativeLink("./js/FileManager/DnD.jar")."';";
		$script[] = "var upload_label = 'Upload';";
		$script[] = "var server_url = '".SOY2PageController::createLink("FileManager.DragAndDrop")."/".$this->id."';";
		
		HTMLHead::addScript("applet",array(
			"script" => implode("\n",$script)
		));
		
		HTMLHead::addScript("deploy",array(
			"src" => "http://java.com/js/deployJava.js"
		));
		
	}
	
}

?>