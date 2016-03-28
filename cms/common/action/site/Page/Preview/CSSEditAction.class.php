<?php

class CSSEditAction extends SOY2Action {

	protected function execute(SOY2ActionRequest &$request,SOY2ActionForm &$form,SOY2ActionResponse &$response){
		
		$url = $form->cssName;
		$siteUrl = UserInfoUtil::getSiteURL();

		//Readability
		$contents = @file_get_contents($url);
	
		if($contents === false){
			$this->setAttribute("url",$url);
			return SOY2Action::FAILED;
		}
		
		//Writability
		$siteDefaultURL = UserInfoUtil::getSiteURLBySiteId(UserInfoUtil::getSite()->getId());
		$siteDir = UserInfoUtil::getSiteDirectory();
		$path = $siteDir . str_replace($siteUrl, "", $url);
		$writable = file_exists($path) && is_writable($path);
		
		$this->setAttribute("url",$url);
		$this->setAttribute("path",$path);
		$this->setAttribute("filename",$url);
		$this->setAttribute("writable",$writable);
		$this->setAttribute("contents",mb_convert_encoding($contents,'UTF-8','auto'));
		
		return SOY2Action::SUCCESS;
    }
    
    function getURIPrefix(){
    	return UserInfoUtil::getSiteUrl();
    }
}

class CSSEditActionForm extends SOY2ActionForm{
	
	var $cssName;
	
	function setCssName($url){
		$this->cssName = $url;
	}
}
?>