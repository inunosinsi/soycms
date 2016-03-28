<?php
/**
 * ラベルの新規作成
 * @attribute id
 */
class LabelCreateAction extends SOY2Action{

    protected function execute(SOY2ActionRequest &$request,SOY2ActionForm &$form,SOY2ActionResponse &$response){

		//記事管理者は操作禁止
		if(class_exists("UserInfoUtil") && !UserInfoUtil::hasSiteAdminRole()){
			return SOY2Action::FAILED;
		}

		if($form->hasError()){
			foreach($form as $key => $value){
				$this->setErrorMessage($key,$form->getErrorString($key));
			}
			return SOY2Action::FAILED;
		}


		SOY2::import("domain.cms.Label");
		$label = SOY2::cast("Label",$form);

		//すでに存在するラベル名と同名のラベルを作成できなくする
		$logic = SOY2Logic::createInstance("logic.site.Label.LabelLogic");
		if(!$logic->checkDuplicateCaption($label->getCaption())){
			$this->setErrorMessage("failed","重複する名称が存在します");
			return SOY2Action::FAILED;
		}

		//CMS:PLUGIN callEventFunction
		CMSPlugin::callEventFunc('onLabelCreate',array("label"=>$label));

		//並び順補正
		$label->setDisplayOrder(Label::ORDER_MAX);


		try{
			$id = $logic->create($label);
			$this->setAttribute("id",$id);
			return SOY2Action::SUCCESS;
		}catch(Exception $e){
			return SOY2Action::FAILED;
		}

	}
}

class LabelCreateActionForm extends SOY2ActionForm{
	var $caption;

	/**
	 * @validator string {"require":true}
	 */
	function setCaption($caption) {
		$this->caption = $caption;
	}
}
?>