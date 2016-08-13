<?php

class IndexPage extends WebPage{

    function __construct() {
    	WebPage::__construct();
    	
    	$dao = SOY2DAOFactory::create("SOYVoice_CommentDAO");
    	$dao->setLimit(10);
    	try{
    		$voices = $dao->getByUserType();
    	}catch(Exception $e){
    		$voices = array();
    	}
    	
    	$this->createAdd("voice_list","PublishedVoiceList",array(
    		"list" => $voices
    	));
    	
    	$this->createAdd("no_list","HTMLModel",array(
    		"visible" => count($voices)==0
    	));
    	
    	$logDao = SOY2DAOFactory::create("SOYVoice_LogDAO");
    	try{
    		$log = $logDao->get();
    		$log = $log[0];
    	}catch(Exception $e){
    		$log = new SOYVoice_Log();
    	}
    	
    	if(is_null($log))$log = new SOYVoice_Log();
	
    	$this->createAdd("last_export_date","HTMLLabel",array(
	   		"text" => (!is_null($log->getExportDate())) ? date("Y-m-d H:i:s",$log->getExportDate()) : "エクスポートはされていません"
    	));	
    	
    }
}

class PublishedVoiceList extends HTMLList{
	
	protected function populateItem($entity){
		
		$this->createAdd("id","HTMLLabel",array(
			"text" => $entity->getId()
		));
		
		$this->createAdd("is_email","HTMLModel",array(
			"visible" => (strlen($entity->getEmail())>0)
		));
		$this->createAdd("no_email","HTMLModel",array(
			"visible" => (strlen($entity->getEmail())==0)
		));
		
		$this->createAdd("email","HTMLLink",array(
			"link" => "mailto:".$entity->getEmail(),
			"text" => $entity->getNickname()
		));
		
		$this->createAdd("nickname","HTMLLabel",array(
			"text" => $entity->getNickname()
		));
		
		$this->createAdd("content","HTMLLabel",array(
			"text" => $entity->getContent()
		));
		
		$this->createAdd("url","HTMLLink",array(
			"link" => $entity->getUrl(),
			"text" => $entity->getUrl(),
			"visible" => (strlen($entity->getUrl())>0)
		));
		
		$this->createAdd("is_published","HTMLLabel",array(
			"text" => ($entity->getIsPublished()==1) ? "公開" : "非公開"
		));
		$this->createAdd("is_entry","HTMLLabel",array(
			"text" => ($entity->getIsEntry()==1) ? "済" : "未"
		));
		
		$this->createAdd("edit_link","HTMLLink",array(
			"link" => SOY2PageController::createLink(APPLICATION_ID.".Comment.Detail.".$entity->getId())
		));
		
		$this->createAdd("remove_link","HTMLLink",array(
			"link" => SOY2PageController::createLink(APPLICATION_ID.".Comment.Remove.".$entity->getId()),
			"onclick" => "return confirm('削除してもよろしいですか？');"
		));
			
	}
	
}
?>