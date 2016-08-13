<?php

class IndexPage extends CMSWebPageBase{

	function doPost(){
		if(isset($_POST["toggle_page_style"])){
			switch($this->getPageListStyle()){
				case "Table":
					$this->updateCookie("Tree");
					break;
				case "Tree":
					$this->updateCookie("Normal");
					break;				
				case "Normal":
				default:					
					$this->updateCookie("Table");
					break;				
			}
		}
		SOY2PageController::jump("Page");
		exit;
	}

    function __construct() {
    	WebPage::__construct();
    	$page = null;
    	switch($this->getPageListStyle()){
    		case "Table":
    			$page = SOY2HTMLFactory::createInstance("Page.List.TablePage");
    			break;
    		case "Tree":
    			$page = SOY2HTMLFactory::createInstance("Page.List.TreePage");
    			break;
    		case "Normal":
    		default:
    			$page = SOY2HTMLFactory::createInstance("Page.List.NormalPage");
    			break;
    	}
    	$page->display();
    	
 		exit;
    }
    
    function updateCookie($style){
		$cookieName = "Page_Style";
		$value = $style;
		$time = time() + 3*30*24*60*60;
		setcookie($cookieName,$value,$time);
	}
	
	function getPageListStyle(){
		if(isset($_COOKIE["Page_Style"])){
			return $_COOKIE["Page_Style"];
		}else{
			return "Normal";
		}
	}
} 	
?>