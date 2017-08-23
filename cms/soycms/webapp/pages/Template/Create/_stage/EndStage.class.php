<?php
class EndStage extends StageBase{

	public function getStageTitle(){
		return "ページ雛形の新規作成 (5/5) - 完了";
	}


	public function execute(){
		//一時ディレクトリの削除
		$this->deleteTempDir();
		$this->wizardObj = null;

		$this->saveWizardObject();

		SOY2ActionSession::getUserSession()->setAttribute("Template.Create.WizardCurrentStage",null);
	}

	public function checkNext(){
		return true;
	}

	public function checkBack(){
		return false;
	}

	public function getNextString(){
		return "";
	}

	public function getBackString(){
		return "";
	}
}
