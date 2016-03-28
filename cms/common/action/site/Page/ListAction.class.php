<?php
/**
 * ページ一覧の取得
 * @attribute list
 */
class ListAction extends SOY2Action{

    protected function execute(SOY2ActionRequest &$request,SOY2ActionForm &$form,SOY2ActionResponse &$response){
    	
    	$logic = SOY2Logic::createInstance("logic.site.Page.PageLogic");
    	
    	try{
    		$pages = $logic->get();
    	}catch(Exception $e){
    		$this->setErrorMessage("failed","ページ一覧の取得に失敗しました");
    		return SOY2Action::FAILED;
    	}
 		
 		$this->setAttribute("list",$pages);
 		
 		return SOY2Action::SUCCESS;
    }
}
?>