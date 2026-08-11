<?php

class SOYShopApplicationNameBase implements SOY2PluginAction{
	
	private $mode;
	
	/**
	 * @return string
	 */
	function getFormFromCartApplicationConfigPage(){

	}

	/**
	 * doPost
	 */
	function doPostFromCartApplicationConfigPage(){

	}

	/**
	 * @return string
	 */
	function getFormFromMypageApplicationConfigPage(){

	}

	/**
	 * doPost
	 */
	function doPostFromMypageApplicationConfigPage(){

	}
	
	function getMode(){
		return $this->mode;
	}
	
	function setMode($mode){
		$this->mode = $mode;
	}
}
class SOYShopApplicationNameDeletageAction implements SOY2PluginDelegateAction{

	private $mode;

	function run($extetensionId, $moduleId, SOY2PluginAction $action){
		
		$action->setMode($this->mode);
		
		switch($this->mode){
			case "cart":
				if(strtolower($_SERVER['REQUEST_METHOD']) == "post"){
					$action->doPostFromCartApplicationConfigPage();
				}else{
					echo $action->getFormFromCartApplicationConfigPage();
				}
				break;
			
			case "mypage":
				if(strtolower($_SERVER['REQUEST_METHOD']) == "post"){
					$action->doPostFromMypageApplicationConfigPage();
				}else{
					echo $action->getFormFromMypageApplicationConfigPage();
				}
				break;
		}
		
			
	}
	
	function setMode($mode){
		$this->mode = $mode;
	}
}
SOYShopPlugin::registerExtension("soyshop.application.name","SOYShopApplicationNameDeletageAction");
?>