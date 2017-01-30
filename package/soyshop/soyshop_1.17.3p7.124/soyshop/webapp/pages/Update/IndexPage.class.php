<?php

class IndexPage extends WebPage{
	
	function __construct(){
		
		//データベースの変更の必要がない場合はトップページに戻す
		$checkVersionLogic = SOY2Logic::createInstance("logic.upgrade.CheckVersionLogic");
		if($checkVersionLogic->checkVersion() === false){
			SOY2PageController::jump("");
		}
		
		$updateDBLogic = SOY2Logic::createInstance("logic.upgrade.UpdateDBLogic");
		
		$updateDBLogic->update();
		
		SOY2PageController::jump("?update=finish");
	}
}

?>