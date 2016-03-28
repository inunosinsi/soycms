<?php
/**
 * ページの詳細を取得します
 * @attribute Page
 */
class DetailAction extends SOY2Action{
	
	/**
	 * ページID
	 */
	var $id;
	
	function setId($id){
		$this->id = $id;
	}
	
    protected function execute(SOY2ActionRequest &$request,SOY2ActionForm &$form,SOY2ActionResponse &$response){
    	
    	$logic = SOY2Logic::createInstance("logic.site.Page.PageLogic");
    	try{
    		$page = $logic->getById($this->id);
    	}catch(Exception $e){
    		$this->setErrorMessage("failed","ページの取得に失敗");
    		return SOY2Action::FAILED;
    	}
    	
    	//無限遠時刻をnullになおす
    	$page->setOpenPeriodEnd(CMSUtil::decodeDate($page->getOpenPeriodEnd()));
		$page->setOpenPeriodStart(CMSUtil::decodeDate($page->getOpenPeriodStart()));
		
		$this->setAttribute("Page",$page);    	
    	
    	
    	return SOY2Action::SUCCESS;
    }
}
?>