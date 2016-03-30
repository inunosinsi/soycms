<?php

class SOYShopItemCustomFieldBase implements SOY2PluginAction{

	/**
	 * @return string
	 */
	function getForm(SOYShop_Item $item){

	}

	/**
	 * doPost
	 */
	function doPost(SOYShop_Item $item){

	}

	/**
	 * onOutput
	 */
	function onOutput($htmlObj, SOYShop_Item $item){

	}

	/**
	 * @onDelete
	 */
	function onDelete($id){


	}

}
class SOYShopItemCustomFieldDeletageAction implements SOY2PluginDelegateAction{

	private $deleteItemId;
	private $item;
	private $htmlObj;
	private $pageObj;

	function run($extetensionId, $moduleId, SOY2PluginAction $action){

		if($this->deleteItemId){
			$action->onDelete($this->deleteItemId);

		}else if($this->htmlObj){
			$action->onOutput($this->htmlObj, $this->item);

		}else{

			if(strtolower($_SERVER['REQUEST_METHOD']) == "post"){
				$action->doPost($this->getItem());
			}else{
				echo $action->getForm($this->getItem());
			}

		}
	}
	function getItem() {
		return $this->item;
	}
	function setItem($item) {
		$this->item = $item;
	}

	function getHtmlObj() {
		return $this->htmlObj;
	}
	function setHtmlObj($htmlObj) {
		$this->htmlObj = $htmlObj;
	}
	function getPageObj() {
		return $this->pageObj;
	}
	function setPageObj($pageObj) {
		$this->pageObj = $pageObj;
	}
	function getDeleteItemId() {
		return $this->deleteItemId;
	}
	function setDeleteItemId($deleteItemId) {
		$this->deleteItemId = $deleteItemId;
	}
}
SOYShopPlugin::registerExtension("soyshop.item.customfield","SOYShopItemCustomFieldDeletageAction");
?>