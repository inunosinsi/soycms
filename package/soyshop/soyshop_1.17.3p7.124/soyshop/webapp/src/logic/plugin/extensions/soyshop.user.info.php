<?php
/*
 * Created on 2009/02/10
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
class SOYShopUserInfoPageBase implements SOY2PluginAction{

	/**
	 * @return string
	 */
	function getMenuTitle(){
	
	}
	
	function getMenuDescription(){
		
	}
}
class SOYShopUserInfoPageDeletageAction implements SOY2PluginDelegateAction{

	private $_list = array();

	function run($extetensionId, $moduleId, SOY2PluginAction $action){
		
		if($action instanceof SOYShopUserInfoPageBase){
			$this->_list[$moduleId] = array(
				"title" => $action->getMenuTitle(),
				"description" => $action->getMenuDescription()
			);
		}
	}
	
	function getList(){
		return $this->_list;
	}
}
SOYShopPlugin::registerExtension("soyshop.user.info", "SOYShopUserInfoPageDeletageAction");
?>