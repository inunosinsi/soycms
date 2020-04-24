<?php

class SOYShopMypageInquiry implements SOY2PluginAction{

	/**
	 * @param void
	 * @return string
	 */
	function getRequirementValue(){
		return "";
	}
}

class SOYShopMypageInquiryDeletageAction implements SOY2PluginDelegateAction{

	private $mode = "requirement";	//sendとeditがある
	private $_value;

	function run($extetensionId, $moduleId, SOY2PluginAction $action){
		switch($this->mode){
			case "requirement":
			default:
				$this->_value = $action->getRequirementValue();
		}
	}

	function getValue(){
		return $this->_value;
	}
	function setMode($mode){
		$this->mode = $mode;
	}
}
SOYShopPlugin::registerExtension("soyshop.mypage.inquiry", "SOYShopMypageInquiryDeletageAction");
