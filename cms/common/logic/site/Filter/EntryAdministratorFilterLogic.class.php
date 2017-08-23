<?php

class EntryAdministratorFilterLogic extends SOY2LogicBase{

	//アクセスを許可するページ
	private $rules = array(
		"^Common",
		"^Simple",
		"^Entry",
		"^Blog",
		"^Page.Preview",
		"^Login",
		"^FileManager",
		"^Plugin.CustomPage",
		"^Page.Editor.FileUpload",
	);

	//例外的にアクセスを禁止するページ
	private $ignoreRules = array(
		"^Page.Preview.Template",
		"^Blog.Template",
		"^Blog.Config",
	);


    function checkAvaiable(){
    	static $requestPath;

    	/**
    	 * リクエストされたパスを取得
    	 * SOY2PageController::getRequestPath()のコピー
    	 */
    	if(is_null($requestPath)){
			$soy2cont = SOY2PageController::init();
			$pathBuilder = $soy2cont->getPathBuilder();
			$path = $pathBuilder->getPath();
			$args = $pathBuilder->getArguments();
			if(!strlen($path) || substr($path,strlen($path)-1,1) == "."){
				$path .= $soy2cont->getDefaultPath();
			}
			$requestPath = $path;
    	}

    	foreach($this->ignoreRules as $rule){
    		if(preg_match("/".$rule."/",$requestPath)){
    			return false;
    		}
    	}

    	foreach($this->rules as $rule){
    		if(preg_match("/".$rule."/",$requestPath)){
    			return true;
    		}
    	}

    	return false;
    }
}
?>