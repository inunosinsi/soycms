<?php

class SOYShopCategoryNameBase implements SOY2PluginAction{

	/**
	 * @return string
	 */
	function getForm(SOYShop_Category $category){

	}

	/**
	 * doPost
	 */
	function doPost(SOYShop_Category $category){

	}
}
class SOYShopCategoryNameDeletageAction implements SOY2PluginDelegateAction{

	private $category;

	function run($extetensionId, $moduleId, SOY2PluginAction $action){
		
		if(strtolower($_SERVER['REQUEST_METHOD']) == "post"){
			$action->doPost($this->getCategory());
		}else{
			echo $action->getForm($this->getCategory());
		}
	}

	function getCategory() {
		return $this->category;
	}
	function setCategory($category) {
		$this->category = $category;
	}
}
SOYShopPlugin::registerExtension("soyshop.category.name","SOYShopCategoryNameDeletageAction");
?>