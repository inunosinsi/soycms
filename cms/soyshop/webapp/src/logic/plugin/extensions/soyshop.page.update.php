<?php
class SOYShopPageUpdate implements SOY2PluginAction{

	function onUpdate($pageId){

	}

	/**
	 * @onDelete
	 */
	function onDelete($id){

	}
}
class SOYShopPageUpdateDeletageAction implements SOY2PluginDelegateAction{

	private $pageId;
	private $deletePageId;

	function run($extetensionId,$moduleId,SOY2PluginAction $action){
		if(is_numeric($this->pageId)){
			$action->onUpdate($this->pageId);
		}

		if(is_numeric($this->deletePageId)){
			$action->onDelete($this->deletePageId);
		}
	}

	function setPageId($pageId){
		$this->pageId = $pageId;
	}
	function setDeletePageId($deletePageId) {
		$this->deletePageId = $deletePageId;
	}


}
SOYShopPlugin::registerExtension("soyshop.page.update", "SOYShopPageUpdateDeletageAction");
