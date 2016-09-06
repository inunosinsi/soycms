<?php
/**
 * 説明の名称の変更を行います
 */
class ChangeLabelIconAction extends SOY2Action{

    protected function execute(SOY2ActionRequest &$request,SOY2ActionForm &$form,SOY2ActionResponse &$response){

		//記事管理者は操作禁止。但し、ブログのカテゴリからの場合はあり
		if(class_exists("UserInfoUtil") && !UserInfoUtil::hasSiteAdminRole() && !strpos($_SERVER["PATH_INFO"], "Blog")){
			return SOY2Action::FAILED;
		}

		//ファイルの存在確認
		$filepath = CMS_LABEL_ICON_DIRECTORY . $form->labelicon;

		if(!strlen($form->labelicon))return SOY2Action::FAILED;
		if(!file_exists($filepath))return SOY2Action::FAILED;

		$logic = SOY2Logic::createInstance("logic.site.Label.LabelLogic");
		$label = null;
		try{
			$label = $logic->getById($form->id);
		}catch(Exception $e){
			return SOY2Action::FAILED;
		}

		//アイコンを適用
		$label->setIcon($form->labelicon);

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

class ChangeLabelIconActionForm extends SOY2ActionForm{
	var $id;
	var $labelicon;

	/**
	 * @validator number {"require":true}
	 */
	function setId($id) {
		$this->id = $id;
	}

	function setLabelicon($filename){
		//..は除く
		$filename = preg_replace("/^\.+/","",$filename);
		$this->labelicon = $filename;
	}
}
?>