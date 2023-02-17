<?php
class CancelUploadFileAction extends SOY2Action{

    function execute($resp, $form, $req) {
    	if(substr($this->getDefaultUpload(),-1) == '/'){
    		$filepath = UserInfoUtil::getSiteDirectory().self::getDefaultUpload().$form->serverpath;
    	}else{
    		$filepath = UserInfoUtil::getSiteDirectory().self::getDefaultUpload().'/'.$form->serverpath;
    	}

    	$resObj = new StdClass();
    	$resObj->mode = "cancel";

    	if(!file_exists($filepath)){
    		$resObj->message = "指定されたファイルが見つかりませんでした";
    		$resObj->result = false;
    	}else{
    		//if(unlink($filepath)){
    		try{
    			$resObj->message ="成功しました";
    			$resObj->result = true;
    		}catch(Exception $e){
    			$resObj->message = "削除に失敗しました";
    			$resObj->result = false;
    		}
    	}
    	$this->setAttribute("result",$resObj);
    }

    private function getDefaultUpload(){
		return soycms_get_site_config_object()->getUploadDirectory();
    }
}

class CancelUploadFileActionForm extends SOY2ActionForm{

	private $serverpath;

	function setServerpath($filepath){
		$this->serverpath  = $filepath;
	}

}
