<?php

class ModuleCreateLogic extends SOY2LogicBase{

	//モジュール名に?{}|&~!()^"が含まれている場合はエラーを返す
	function validate($moduleName){
		if(preg_match('/\?|{|}|&|~|!|\(|\)|\^|"/', $moduleName, $tmp)){
			return false;
		}

		return true;
	}
}
