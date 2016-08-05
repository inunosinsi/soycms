<?php
/**
 * @class LabelUpdateAction
 * @date 2008-03-24T20:52:37+09:00
 * @author SOY2ActionFactory
 */
class LabelUpdateAction extends SOY2Action{

	private $id;

	function setId($id){
		$this->id = $id;
	}


	/**
	 * Actionの実行を行います。
	 */
	protected function execute(SOY2ActionRequest &$request,SOY2ActionForm &$form, SOY2ActionResponse &$response){
		
		//記事管理者は操作禁止
		if(class_exists("UserInfoUtil") && !UserInfoUtil::hasSiteAdminRole()){
			return SOY2Action::FAILED;
		}

		//フォームにエラーが発生していた場合
		if($form->hasError()){
			foreach($form->getErrors() as $key => $value){
				$this->setErrorMessage($key,$form->getErrorString($key));
			}
			return SOY2Action::FAILED;
		}
		
		if(isset($form->alias) && is_numeric($form->alias)){
			$this->setErrorMessage("failed","URLで数字は使用できません");
			return SOY2Action::FAILED;
		}

		$logic = SOY2Logic::createInstance("logic.site.Label.LabelLogic");
		$label = $logic->getById($this->id);
		$label = SOY2::cast($label,$form);
		
		//すでに存在するラベル名と同名のラベルを作成できなくする
		if(!$logic->checkDuplicateCaption($label->getCaption(), $label->getId())){
			$this->setErrorMessage("failed","重複する名称が存在します");
			return SOY2Action::FAILED;
		}

		//CMS:PLUGIN callEventFunction
		CMSPlugin::callEventFunc('onLabelUpdate',array("label"=>$label));

		try{
			$id = $logic->update($label);
			return SOY2Action::SUCCESS;
		}catch(Exception $e){
			return SOY2Action::FAILED;
		}
	}
}

class LabelUpdateActionForm extends SOY2ActionForm{
	var $caption;
	var $alias;
	var $icon;
	var $description;
	var $color;
	var $backgroundColor;

	/**
	 * @validator string {"require":true}
	 */
	function setCaption($caption){
		$this->caption = $caption;
	}
	
	/**
	 * @validator string {}
	 */
	function setAlias($alias){
		$this->alias = $alias;
	}



	/**
	 * @validator string {}
	 */
	function setIcon($icon){
		$this->icon = $icon;
	}


	/**
	 * @validator string {}
	 */
	function setDescription($description){
		$this->description = $description;
	}


	/**
	 * @validator string {}
	 */
	function setColor($color){
		$this->color = hexdec($color);
	}


	/**
	 * @validator string {}
	 */
	function setBackgroundColor($backgroundColor){
		$this->backgroundColor = hexdec($backgroundColor);
	}

}
?>