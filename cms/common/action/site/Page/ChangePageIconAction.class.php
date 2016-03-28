<?php
/**
 * ページアイコンの変更を行います
 */
class ChangePageIconAction extends SOY2Action{

    protected function execute(SOY2ActionRequest &$request,SOY2ActionForm &$form,SOY2ActionResponse &$response){
		
		//ファイルの存在確認
		$filepath = CMS_PAGE_ICON_DIRECTORY . $form->pageicon;
		
		if(!strlen($form->pageicon))return SOY2Action::FAILED;
		if(!file_exists($filepath))return SOY2Action::FAILED;
		
		$logic = SOY2Logic::createInstance("logic.site.Page.PageLogic");
		$page = null;
		try{
			$page = $logic->getById($form->id);
		}catch(Exception $e){
			return SOY2Action::FAILED;
		}
		
		//アイコンを適用
		$page->setIcon($form->pageicon);
		
		try{
			$logic->update($page);
			return SOY2Action::SUCCESS;
		}catch(Exception $e){
			return SOY2Action::FAILED;
		}
	}
}

class ChangePageIconActionForm extends SOY2ActionForm{
	var $id;
	var $pageicon;
	
	/**
	 * @validator number {"require":true}
	 */
	function setId($id) {
		$this->id = $id;
	}
	
	function setPageicon($filename){
		//..は除く
		$filename = preg_replace("/^\.+/","",$filename);
		$this->pageicon = $filename;
	}
}
?>