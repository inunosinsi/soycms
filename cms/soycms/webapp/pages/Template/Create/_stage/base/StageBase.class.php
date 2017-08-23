<?php

class StageBase extends CMSWebPageBase{

	public $wizardObj;

	//表示部分はここに書く
	public function execute(){
	}

	//ステージ名
	public function getStageTitle(){
		return "ステージ (1/5)";
	}


	//次へが押された際の動作
	public function checkNext(){
		return true;
	}

	//前へが押された際の動作
	public function checkBack(){
		return true;
	}

	//次のオブジェクト名、終了の際はEndStageを呼び出す
	public function getNextObject(){
		return "EndStage";
	}

	//前のオブジェクト名、nullの場合は表示しない
	public function getBackObject(){
		return null;
	}

	public function getWizardObj() {
		return $this->wizardObj;
	}
	function setWizardObj($wizardObj) {
		$this->wizardObj = $wizardObj;
	}

	public function getNextString(){
		return CMSMessageManager::get("SOYCMS_WIZARD_NEXT");
	}

	public function getBackString(){
		return CMSMessageManager::get("SOYCMS_WIZARD_PREV");
	}

	public function getTempDir(){
		$tmpDir = ServerInfoUtil::sys_get_writable_temp_dir() . "/" . $this->wizardObj->template->getId();
		if(!file_exists($tmpDir))mkdir($tmpDir);
		return $tmpDir;
	}

	public function deleteTempDir(){
		if (is_null($this->wizardObj) OR is_null($this->wizardObj->template)) return;

		$tmpDir = ServerInfoUtil::sys_get_writable_temp_dir() . "/" . $this->wizardObj->template->getId();
		if(!file_exists($tmpDir))return;
		$files = scandir($tmpDir);

		foreach($files as $file){
			if($file[0] == ".")continue;
			@unlink(realpath($tmpDir."/".$file));
		}

		@rmdir(realpath($tmpDir));

	}


	public function saveWizardObject(){
		SOY2ActionSession::getUserSession()->setAttribute("Template.Create.WizardObject",serialize($this->wizardObj));
	}
}


?>