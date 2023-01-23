<?php

class ModuleCreateLogic extends SOY2LogicBase{

	/**
	 * モジュール名に?{}|&~!()^"が含まれている場合はエラーを返す
	 * @param string
	 * @return bool
	 */
	function validate(string $moduleName){
		if(preg_match('/\?|{|}|&|~|!|\(|\)|\^|"/', $moduleName, $tmp)){
			return false;
		}

		return true;
	}
}
