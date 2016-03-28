<?php
SOY2::import("util.CMSFileManager");
class CancelUploadFileAction extends SOY2Action{

    function execute($response,$form,$request) {
    	if(substr($this->getDefaultUpload(),-1) == '/'){
    		$filepath = UserInfoUtil::getSiteDirectory().$this->getDefaultUpload().$form->serverpath;
    	}else{
    		$filepath = UserInfoUtil::getSiteDirectory().$this->getDefaultUpload().'/'.$form->serverpath;
    	}
    	
    	$resObj = new StdClass();
    	$resObj->mode = "cancel";
    	
    	if(!file_exists($filepath)){
    		$resObj->message = "指定されたファイルが見つかりませんでした";
    		$resObj->result = false;
    	}else{
    		//if(unlink($filepath)){
    		try{
    			
    			CMSFileManager::delete(UserInfoUtil::getSiteDirectory(),realpath($filepath));
    		
    			$resObj->message ="成功しました";
    			$resObj->result = true;	
    		}catch(Exception $e){
    			$resObj->message = "削除に失敗しました";
    			$resObj->result = false;	
    		}	
    	}
    	$this->setAttribute("result",$resObj);
    }
    
    function getDefaultUpload(){
    	
    	$dao = SOY2DAOFactory::create("cms.SiteConfigDAO");
    	$config = $dao->get();
    	$dir = $config->getUploadDirectory();
    	
    	return $dir;
    }
}

class CancelUploadFileActionForm extends SOY2ActionForm{

	var $serverpath;
	
	function setServerpath($filepath){
		$this->serverpath  = $filepath;
	}

}
?>