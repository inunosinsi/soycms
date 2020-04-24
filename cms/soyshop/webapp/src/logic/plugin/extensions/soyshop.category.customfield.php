<?php

class SOYShopCategoryCustomFieldBase implements SOY2PluginAction{

	/**
	 * @return string
	 */
	function getForm($category){
	}

	/**
	 * doPost
	 */
	function doPost($category){

	}

	/**
	 * @onDelete
	 */
	function onDelete($id){


	}

}
class SOYShopCategoryCustomFieldDeletageAction implements SOY2PluginDelegateAction{

	private $deleteCategoryId;
	private $category;
	private $htmlObj;

	function run($extetensionId,$moduleId,SOY2PluginAction $action){

		if($this->deleteCategoryId){
			$action->onDelete($this->deleteCategoryId);

		}else{

			if(strtolower($_SERVER['REQUEST_METHOD']) == "post"){
				$action->doPost($this->getCategory());
			}else{
				echo $action->getForm($this->getCategory());
			}

		}
	}
	function getCategory() {
		return $this->category;
	}
	function setCategory($category) {
		$this->category = $category;
	}

	function getHtmlObj() {
		return $this->htmlObj;
	}
	function setHtmlObj($htmlObj) {
		$this->htmlObj = $htmlObj;
	}
	function getDeleteCategoryId() {
		return $this->deleteCategoryId;
	}
	function setDeleteCategoryId($deleteCategoryId) {
		$this->deleteCategoryId = $deleteCategoryId;
	}
}
SOYShopPlugin::registerExtension("soyshop.category.customfield","SOYShopCategoryCustomFieldDeletageAction");
?>