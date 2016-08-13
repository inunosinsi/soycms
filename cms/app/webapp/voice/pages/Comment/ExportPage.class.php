<?php

class ExportPage extends WebPage{
	
	function doPost(){
		
		if(soy2_check_token()&&isset($_POST["Export"])){
			$count = count($_POST["Export"]);
			$logic = SOY2Logic::createInstance("logic.SyncLogic");
			$res = $logic->sync($_POST["Export"]);
			
			if($res){
				$dao = SOY2DAOFactory::create("SOYVoice_LogDAO");
				$obj = new SOYVoice_Log();
				$obj->setCount($count);
				$obj->setExportDate(time());
				try{
					$dao->insert($obj);
					CMSApplication::jump("Comment.Export");
				}catch(Exception $e){
					
				}
			}

			CMSApplication::jump("Comment.Export");
		}
	}

    function __construct() {
    	WebPage::__construct();
    	
    	$dao = SOY2DAOFactory::create("SOYVoice_CommentDAO");
    	
    	try{
    		$voices = $dao->getCommentNoEntry();
    	}catch(Exception $e){
    		$voices = array();
    	}
    	
    	$this->createAdd("form","HTMLForm");
    	
    	$this->createAdd("voice_list","VoiceListNoEntry",array(
    		"list" => $voices
    	));
    	
    	$this->createAdd("no_list","HTMLModel",array(
    		"visible" => (count($voices)==0)
    	));
    }
}

class VoiceListNoEntry extends HTMLList{
	
	protected function populateItem($entity){
		
		$this->createAdd("check","HTMLCheckBox",array(
			"name" => "Export[]",
			"value" => $entity->getId(),
			"selected" => true
		));
		
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
	
	}
	
}
?>