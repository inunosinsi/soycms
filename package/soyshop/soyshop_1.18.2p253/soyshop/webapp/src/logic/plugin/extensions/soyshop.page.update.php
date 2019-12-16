<?php
class SOYShopPageUpdate implements SOY2PluginAction{

	/**
	 * @onDelete
	 */
	function onDelete($id){

	}
}
class SOYShopPageUpdateDeletageAction implements SOY2PluginDelegateAction{

	private $deletePageId;

	function run($extetensionId,$moduleId,SOY2PluginAction $action){
		if($this->deletePageId){
			$action->onDelete($this->deletePageId);
		}
	}
	
	function getDeletePageId() {
		return $this->deletePageId;
	}
	function setDeletePageId($deletePageId) {
		$this->deletePageId = $deletePageId;
	}
	
	
}
SOYShopPlugin::registerExtension("soyshop.page.update","SOYShopPageUpdateDeletageAction");
?>