<?php
/**
 * 説明の名称の変更を行います
 */
class ReDescriptionAction extends SOY2Action{

    protected function execute(SOY2ActionRequest &$request,SOY2ActionForm &$form,SOY2ActionResponse &$response){

		//記事管理者は操作禁止。但し、ブログのカテゴリからの場合はあり
		if(class_exists("UserInfoUtil") && !UserInfoUtil::hasSiteAdminRole() && !strpos($_SERVER["PATH_INFO"], "Blog")){
			return SOY2Action::FAILED;
		}

		$logic = SOY2Logic::createInstance("logic.site.Label.LabelLogic");

		try{
			$label = $logic->getById($form->id);
		}catch(Exception $e){
			return SOY2Action::FAILED;
		}

		SOY2::import("domain.cms.Label");
		$label2 = SOY2::cast("Label",$form);

		$label->setDescription($label2->getDescription());

		//CMS:PLUGIN callEventFunction
		CMSPlugin::callEventFunc('onLabelUpdate',array("new_label"=>$label));

		try{
			$logic->update($label);
			return SOY2Action::SUCCESS;
		}catch(Exception $e){
			$this->setErrorMessage("failed","ラベルの名称変更に失敗しました");
			return SOY2Action::FAILED;
		}
	}
}

class ReDescriptionActionForm extends SOY2ActionForm{
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