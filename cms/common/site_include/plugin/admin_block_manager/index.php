<?php

function get_config_page(){
	
	@session_start();
	
	if(!isset($_SESSION[ADMIN_BLOCK_MANAGER]))$_SESSION[ADMIN_BLOCK_MANAGER] = array();
	$session = $_SESSION[ADMIN_BLOCK_MANAGER];
		
	$mode = @$session["mode"];
	
	if(isset($_GET["mode"])){
		$mode = $_GET["mode"];
	}
	
	SOY2::import("domain.cms.Block");
	
	switch($mode){
		
		case 1:
			
			include_once(dirname(__FILE__)."/files/ABM_ConfirmPage.class.php");
			$page = SOY2HTMLFactory::createInstance("ABM_ConfirmPage",array(
				"arguments" => array(
					"mode" => $mode
				)
			));
			
			break;
			
		case 2:
			
			include_once(dirname(__FILE__)."/files/ABM_DetailPage.class.php");
			$page = SOY2HTMLFactory::createInstance("ABM_DetailPage",array(
				"arguments" => array(
					"mode" => $mode
				)
			));
			
			ob_start();
			$page->display();
			$html = ob_get_contents();
			ob_end_clean();
			
			echo $html;
			exit;
			
			break;
			
		case 0:
		default:
			include_once(dirname(__FILE__)."/files/ABM_StartPage.class.php");
			$page = SOY2HTMLFactory::createInstance("ABM_StartPage",array(
				"arguments" => array(
					"mode" => $mode
				)
			));			
			break;
		
	}
	
	ob_start();
	$page->display();
	$html = ob_get_contents();
	ob_end_clean();
	
	return $html;
}


class ABM_PageBase extends WebPage{
	
	function goNext($value = 1){
		if(!isset($_SESSION[ADMIN_BLOCK_MANAGER]))$_SESSION[ADMIN_BLOCK_MANAGER] = array();
		$session = $_SESSION[ADMIN_BLOCK_MANAGER];
		
		@$session["mode"] = $value;
		
		$this->saveSession($session);
		CMSPlugin::redirectConfigPage();
	}
	
	function goBack($value = 0){
		if(!isset($_SESSION[ADMIN_BLOCK_MANAGER]))$_SESSION[ADMIN_BLOCK_MANAGER] = array();
		$session = $_SESSION[ADMIN_BLOCK_MANAGER];
		
		@$session["mode"] = $value;
		$this->saveSession($session);
		
		CMSPlugin::redirectConfigPage();
	}
	
	function &getSession(){
		if(!isset($_SESSION[ADMIN_BLOCK_MANAGER]))$_SESSION[ADMIN_BLOCK_MANAGER] = array();
		return $_SESSION[ADMIN_BLOCK_MANAGER];
	}
	
	function saveSession($array){
		$_SESSION[ADMIN_BLOCK_MANAGER] = $array;
	}
	
	function createBlock(){
		$session = $this->getSession();
		$class = $session["class"];
		$block = new Block();
		$block->setSoyId($session["block_id"]);
		$block->setClass($class);
		
		if(isset($session["object"]) && is_object($session["object"])){
			$component = $block->getBlockComponent();
			SOY2::cast($component,$session["object"]);
			$block->setObject($component);
		}
			
		
		return $block;
	}
}
?>