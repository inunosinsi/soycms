<?php
/**
 * ラベルの名称の変更を行います
 */
class RenameAction extends SOY2Action{

    protected function execute(SOY2ActionRequest &$request,SOY2ActionForm &$form,SOY2ActionResponse &$response){

		//記事管理者は操作禁止。但し、ブログのカテゴリからの場合はあり
		if(class_exists("UserInfoUtil") && !UserInfoUtil::hasSiteAdminRole() && !strpos($_SERVER["PATH_INFO"], "Blog")){
			return SOY2Action::FAILED;
		}

		SOY2::import("domain.cms.Label");
		$label = SOY2::cast("Label",$form);

		$logic = SOY2Logic::createInstance("logic.site.Label.LabelLogic");

		//CMS:PLUGIN callEventFunction
		CMSPlugin::callEventFunc('onLabelUpdate',array("new_label"=>$label));

		if(!$logic->checkDuplicateCaption($label->getCaption())){
			$this->setErrorMessage("failed","重複する名称が存在します");
			return SOY2Action::FAILED;
		}

		try{
			$logic->update($label);
			return SOY2Action::SUCCESS;
		}catch(Exception $e){
			$this->setErrorMessage("failed","ラベルの名称変更に失敗しました");
			return SOY2Action::FAILED;
		}
	}
}

class RenameActionForm extends SOY2ActionForm{
	var $id;
	var $caption;
	var $description;

	/**
	 * @validator number {"require":true}
	 */
	function setId($id) {
		$this->id = $id;
	}

	/**
	 * @validator string {"require":true}
	 */
	function setCaption($caption) {
		$this->caption = $caption;
	}

	function setDescription($description){
		$this->description = $description;
	}
}
?>