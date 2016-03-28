<?php

class StageBase extends CMSWebPageBase{
	
	var $wizardObj;
	
	

    function StageBase() {
    	WebPage::WebPage();
    }
    
    //表示部分はここに書く
    function execute(){		
    }
    
    //次へが押された際の動作
    function checkNext(){
    	return true;
    }
    
    //前へが押された際の動作
    function checkBack(){
    	return true;
    }
    
    //次のオブジェクト名、終了の際はEndStageを呼び出す
    function getNextObject(){
    	return "EndStage";
    }
    
    //前のオブジェクト名、nullの場合は表示しない
    function getBackObject(){
    	return null;
    }

    function getWizardObj() {
    	return $this->wizardObj;
    }
    function setWizardObj($wizardObj) {
    	$this->wizardObj = $wizardObj;
    }
    
    function getNextString(){
    	return "次へ";
    }
    
    function getBackString(){
    	return "前へ";
    }
}
?>