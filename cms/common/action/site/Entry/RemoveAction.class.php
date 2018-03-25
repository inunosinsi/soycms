<?php

SOY2::import("base.validator.SOY2ActionFormValidator_ArrayValidator");

/**
 * エントリーを削除します
 */
class RemoveAction extends SOY2Action{

	/**
	 * Entry.idを直接指定
	 */
	private $id;

	public function setId($id){
		$this->id = $id;
	}

    protected function execute(SOY2ActionRequest &$request,SOY2ActionForm &$form,SOY2ActionResponse &$response){

		if($this->id){
			$entryIds = array($this->id);
		}else{
			if($form->hasError()){
				foreach($form as $key => $value){
					$this->setErrorMessage($key,$form->getErrorString($key));
				}
				return SOY2Action::FAILED;
			}

			$entryIds = $form->getEntry();
		}

		if(SOY2Logic::createInstance("logic.site.Entry.EntryLogic")->deleteByIds($entryIds)){
			foreach($entryIds as $id){
				//CMS:PLUGIN callEventFunction
				CMSPlugin::callEventFunc('onEntryRemove',array("entryId"=>$id));
			}

			return SOY2Action::SUCCESS;

		}else{
			return SOY2Action::FAILED;
		}
    }
}

class RemoveActionForm extends SOY2ActionForm{

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
