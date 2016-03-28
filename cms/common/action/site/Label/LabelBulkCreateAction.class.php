<?php
/**
 * ラベルの一括新規作成
 */
class LabelBulkCreateAction extends SOY2Action{

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

		//SOY2::import("domain.cms.Label");
		$labelDAO = SOY2DAOFactory::create("cms.LabelDAO");
		$logic = SOY2Logic::createInstance("logic.site.Label.LabelLogic");

		$labelCaptions = explode("\n", $form->captions);

		try{
			$labelDAO->begin();
			foreach($labelCaptions as $caption){
				$caption = trim($caption);

				if(strlen($caption) == 0){
					continue;
				}

				$label = new Label();
				$label->setCaption($caption);
				$label->setDisplayOrder(Label::ORDER_MAX);

				//すでに存在するラベル名と同名のラベルを作成できなくする
				if(!$logic->checkDuplicateCaption($label->getCaption())){
					$this->setErrorMessage("captions", "Duplicate caption: $caption");
					throw new Exception("Duplicate caption: $caption");
				}

				//CMS:PLUGIN callEventFunction
				CMSPlugin::callEventFunc('onLabelCreate',array("label"=>$label));

				$id = $logic->create($label);
			}
			$labelDAO->commit();
		}catch(Exception $e){
			return SOY2Action::FAILED;
		}

		return SOY2Action::SUCCESS;

	}
}

class LabelBulkCreateActionForm extends SOY2ActionForm{
	var $captions;

	/**
	 * @validator string {"require":true}
	 */
	function setCaptions($captions) {
		$captions = str_replace(array("\r\n", "\r"), "\n", $captions);
		$this->captions = $captions;
	}
}
?>