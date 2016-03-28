<?php
/**
 * ページをゴミ箱へ移動させる
 */
class PutTrashAction extends SOY2Action{

	/**
	 * ページID
	 */
	private $id;
	
	function setId($id){
		$this->id= $id;
	}

    protected function execute(SOY2ActionRequest &$request,SOY2ActionForm &$form,SOY2ActionResponse &$response){

		$logic = SOY2Logic::createInstance("logic.site.Page.PageLogic");
		
		//CMS:PLUGIN callEventFunction
		CMSPlugin::callEventFunc('onPutTrash',array("pageId"=>$this->id));
		
		if($logic->putTrash($this->id)){
			return SOY2Action::SUCCESS;
		}else{
			return SOY2Action::FAILED;
		}
	}
}
?>