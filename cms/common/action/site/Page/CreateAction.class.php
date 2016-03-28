<?php
SOY2::import("action.site.Page.PageActionForm");
/**
 * ページを作成します
 * @attribute id
 */
class CreateAction extends SOY2Action{

    protected function execute(SOY2ActionRequest &$request,SOY2ActionForm &$form,SOY2ActionResponse &$response){
		if($form->hasError()){
			$this->setErrorMessage("failed","ページの作成に失敗しました。");
			return SOY2Action::FAILED;
		}		
		
		$dao = SOY2DAOFactory::create("cms.PageDAO");
		$entity = SOY2::cast("Page",$form);
		
		//無限遠時刻、無限近時刻を設定 トリガーに移した
		//$entity->setOpenPeriodEnd(CMSUtil::encodeDate($entity->getOpenPeriodEnd(),false));
		//$entity->setOpenPeriodStart(CMSUtil::encodeDate($entity->getOpenPeriodStart(),true));
		
		//タイトルが空の場合
		if(!$entity->getTitle()){
			$entity->setTitle("newPage");
		}
		
		//CMS:PLUGIN callEventFunction
		CMSPlugin::callEventFunc('onPageCreate',array("page"=>$entity));
		
		$logic = SOY2Logic::createInstance("logic.site.Page.CreatePageLogic");
		try{
			$id = $logic->create($entity,$entity->getTemplate());
			$this->setAttribute("id",$id);
			$this->setAttribute("pageType",$entity->getPageType());
		}catch(Exception $e){
			$this->setErrorMessage("failed",$e->getMessage());
			return SOY2Action::FAILED;
		}
		
		return SOY2Action::SUCCESS;
		    
    }
    
    function getActionFormName(){
    	return "PageActionForm";
    }
    
}

?>