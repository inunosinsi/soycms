<?php
/**
 * ラベルの削除
 */
class RemoveAction extends SOY2Action{

	/**
	 * ラベルID
	 */
	private $id;

	function setId($id){
		$this->id = $id;
	}

	const ERROR_BLOG_FLIPED = 1;
	const ERROR_OTHER	= 2;

	protected function execute(SOY2ActionRequest &$request,SOY2ActionForm &$form,SOY2ActionResponse &$response){

		//記事管理者は操作禁止。但し、ブログのカテゴリからの場合はあり
		if(class_exists("UserInfoUtil") && !UserInfoUtil::hasSiteAdminRole() && !strpos($_SERVER["PATH_INFO"], "Blog")){
			return SOY2Action::FAILED;
		}

		if(is_null($this->id)){
			$this->setErrorMessage("failed","ラベルのIDが指定されていません");
			return SOY2Action::FAILED;
		}

		$logic = SOY2Logic::createInstance("logic.site.Label.LabelLogic");

		//CMS:PLUGIN callEventFunction
		CMSPlugin::callEventFunc('onLabelRemove',array("labelId"=>$this->id));

		try{
			if($logic->delete($this->id)){
				return SOY2Action::SUCCESS;
			}else{
				$this->setAttribute("error_code",RemoveAction::ERROR_BLOG_FLIPED);
				return SOY2Action::FAILED;
			}
		}catch(Exception $e){
			$this->setAttribute("error_code",RemoveAction::ERROR_OTHER);
			return SOY2Action::FAILED;
		}
    }
}
?>