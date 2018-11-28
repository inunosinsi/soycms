<?php

class ABM_DetailPage extends ABM_PageBase{

	function doPost(){

		if(isset($_POST["object"])){

			//保存する
			$obj = (object)$_POST["object"];
			$session = $this->getSession();
			$session["object"] = $obj;
			$this->saveSession($session);

			if(isset($_POST["after_submit"]) && $_POST["after_submit"] == "reload"){
				CMSPlugin::redirectConfigPage();

			}else{
				header("Content-Type: text/html; charset=utf-8;");
				echo '<meta http-equiv="content-type" content="text/html; charset=UTF-8" />';
				echo "<script type=\"text/javascript\">";
				echo "window.parent.common_close_layer(window.parent);";
				echo "</script>";

			}

			exit;
		}



	}

    function __construct(){
    	parent::__construct();

    	$session = $this->getSession();

    	if(!isset($session["class"])){
    		$this->goBack();
    		exit;
    	}

    	$_SERVER["PHP_SELF"] = SOY2PageController::createLink("Plugin.Config?admin_block_manager&mode=2");

    	$block = $this->createBlock();

    	$component = $block->getBlockComponent();
    	$component->blockId = "0";	//dummy

    	$this->add("config",$component->getFormPage());

    }

    function getTemplateFilePath(){
		return dirname(__FILE__) . "/ABM_DetailPage.html";
	}
}
