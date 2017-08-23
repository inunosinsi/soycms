<?php

class FailedStage extends StageBase{

	public function getStageTitle(){
		return "エラー";
	}

	public function getNextString(){
		return "";
	}

	public function getBackString(){
		return "";
	}
}
