<?php
class IndexPage extends CMSWebPageBase{


	function __construct(){
		parent::__construct();

		if(!UserInfoUtil::isDefaultUser()){
    		$this->jump("");
		}


		include(SOY2::RootDir() . "error/error.func.php");

		$this->addTextArea("server_info", array(
			"text" => get_soycms_report() . "\n\n" . get_soycms_options() . "\n\n" . get_environment_report(),
			"style" => "width:100%;height:1000px;border-style:none;",
			"readonly" => "readonly"
		));
		$this->addModel("php_info", array(
			"src" => SOY2PageController::createLink("Server.PHPInfo"),
			"style" => "width:100%;height:1000px;border-style:none;",
		));
	}


}