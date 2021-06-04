<?php
/**
 * ブログに割り当てられているラベルを取得する
 * @attribute list
 */
class BlogLabelListAction extends SOY2Action{

	private $pageId;

	function setPageId($pageId){
		$this->pageId = $pageId;
	}

    protected function execute(SOY2ActionRequest &$request,SOY2ActionForm &$form,SOY2ActionResponse &$response){
		$logic = SOY2Logic::createInstance("logic.site.Label.LabelLogic");
		$require_label = $logic->getBlogLabelByPageId($this->pageId);


		try{
			$labels = $logic->getBlogCategoryLabelsByPageId($this->pageId);
			if(!in_array($require_label,$labels)){
				$labels[$require_label->getId()] = $require_label;
			}
			$this->setAttribute("list",$labels);
		}catch(Exception $e){
			$this->setErrorMessage("failed",$e->getMessage());
			return SOY2Action::FAILED;
		}
		return SOY2Action::SUCCESS;
	}
}
?>
