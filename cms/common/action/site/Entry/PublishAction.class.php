<?php

SOY2::import("base.validator.SOY2ActionFormValidator_ArrayValidator");

/**
 * 公開設定の変更を行います
 */
class PublishAction extends SOY2Action{

	/**
	 * Entry.idを直接指定
	 */
	private $id;

	/**
	 * 公開状態
	 */
	private $publish;

	public function setId($id){
		$this->id = $id;
	}
	function setPublish($publish){
		$this->publish = $publish;
	}

    protected function execute(SOY2ActionRequest &$request,SOY2ActionForm &$form,SOY2ActionResponse &$response){

		if($this->id){
			$entries = array($this->id);
		}else{
			if($form->hasError()){
				foreach($form as $key => $value){
					$this->setErrorMessage($key,$form->getErrorString($key));
				}
				return SOY2Action::FAILED;
			}

			$entries = $form->getEntry();
		}

		if(SOY2LogicContainer::get("logic.site.Entry.EntryLogic")->setPublish($entries,$this->publish)){

			//履歴も更新する
			// TODO EntryLogicのトランザクションに含めたい
			SOY2LogicContainer::get("logic.site.Entry.EntryHistoryLogic")->onPublish($entries,$this->publish);

			return SOY2Action::SUCCESS;
		}else{
			return SOY2Action::FAILED;
		}
	}
}


class PublishActionForm extends SOY2ActionForm{

	private $entry;

	function getEntry(){
		return $this->entry;
	}

	/**
	 * @validator Array {"type":"number"}
	 */
	function setEntry($entry){
		$this->entry = $entry;
	}
}
