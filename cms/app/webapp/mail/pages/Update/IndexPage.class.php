<?php

class IndexPage extends WebPage{

	function __construct(){

		//データベースの変更の必要がない場合はトップページに戻す
		if(SOY2Logic::createInstance("logic.upgrade.CheckVersionLogic")->checkVersion() === false){
			SOY2PageController::jump("");
		}

		SOY2Logic::createInstance("logic.upgrade.UpdateDBLogic")->update();

		SOY2PageController::jump("mail?update=finish");
	}
}
