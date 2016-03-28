<?php

class CSSUpdateAction extends SOY2Action {

	protected function execute(SOY2ActionRequest &$request,SOY2ActionForm &$form,SOY2ActionResponse &$response){
		
		$address = $form->filePath;

		//$address = UserInfoUtil::url2serverpath($address);
		$siteUrl = UserInfoUtil::getSiteURL();
		$siteDefaultURL = UserInfoUtil::getSiteURLBySiteId(UserInfoUtil::getSite()->getId());
		$siteDir = UserInfoUtil::getSiteDirectory();
		$path = $siteDir . str_replace($siteUrl, "", $address);
		
		if(!file_exists($path) OR !is_writable($path)){
			return SOY2Action::FAILED;
		}
		
		$original_contents = @file_get_contents($path);
		if($original_contents !== false){
			$encoding = mb_detect_encoding($original_contents);
			$contents = mb_convert_encoding($form->contents, "UTF-8", $encoding);
		}else{
			return SOY2Action::FAILED;
		}
		
		if(@file_put_contents($path, $contents) !== false){
			return SOY2Action::SUCCESS;
		}else{
			return SOY2Action::FAILED;
		}
		
    }
}

class CSSUpdateActionForm extends SOY2ActionForm {
	var $filePath;
	var $contents;
	
	function setFilePath($file){
		$this->filePath = $file;
	}
	
	function setContents($content){
		$this->contents = $content;
	}
}
?>