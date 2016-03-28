<?php
/**
 * ページを削除します
 */
class RemoveAction extends SOY2Action{

	/**
	 * ページID
	 */
	private $id;
    function setId($id) {
    	$this->id = $id;
    }
	
    protected function execute(SOY2ActionRequest &$request,SOY2ActionForm &$form,SOY2ActionResponse &$response){
		
		$logic = SOY2Logic::createInstance("logic.site.Page.PageLogic");
		
		//CMS:PLUGIN callEventFunction
		CMSPlugin::callEventFunc('onPageRemove',array("pageId"=>$this->id));
		
		if($logic->removePage($this->id)){
			return SOY2Action::SUCCESS;
		}else{
			$this->setErrorMessage("failed","ページの削除に失敗しました。");
			return SOY2Action::FAILED;
		}
	}

}
?>