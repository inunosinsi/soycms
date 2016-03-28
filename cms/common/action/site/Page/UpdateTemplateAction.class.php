<?php
/**
 * テンプレートの保存をします。
 * @init pageId
 */
class UpdateTemplateAction extends SOY2Action {

	private $id;
	
	function setId($pageId){
		$this->id = $pageId;
	}

	protected function execute(SOY2ActionRequest &$request,SOY2ActionForm &$form,SOY2ActionResponse &$response){
		
		$logic = SOY2Logic::createInstance("logic.site.Page.PageLogic");
		$entity = $logic->getById($this->id);
		$entity->setTemplate($_POST["template"]);
		
		try{
			$logic->update($entity);
			
			CMSUtil::notifyUpdate();
			
		}catch(Exception $e){
			$this->setErrorMessage("failed","ページの更新に失敗しました");
			return SOY2Action::FAILED;
		}
				
		return SOY2Action::SUCCESS;
    }
}
?>