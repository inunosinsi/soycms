<?php

class FormLogic extends SOY2LogicBase{
	var $form;
	
    /**
     * フォームの新規作成
     */
    function createForm($form){
    	$this->form = $form;
    	
    	try{
	    	$dao = SOY2DAOFactory::create("SOYInquiry_FormDAO");
	    	
	    	$this->setNotifyMailSubject($form);
	    	$this->setIsUseCaptcha($form);
	    	
	    	$id = $dao->insert($form);
	    	
	    	$columnDao = SOY2DAOFactory::create("SOYInquiry_ColumnDAO");
	    	
	    	$columnDao->begin();
	    	
	    	$column = new SOYInquiry_Column();
    		$column->setFormId($id);
    		$column->setLabel("お名前");
    		$column->setType("SingleText");
    		$column->setRequire(true);
			$columnObject = $column->getColumn();
			$columnObject->setReplacement("#NAME#");
   			$column->setColumn($columnObject);
    		$columnDao->insert($column);
    		
    		$column = new SOYInquiry_Column();
    		$column->setFormId($id);
    		$column->setLabel("メールアドレス");
    		$column->setType("MailAddress");
    		$column->setRequire(true);
    		$columnObject = $column->getColumn();
			$columnObject->setReplacement("#EMAIL#");
			$columnObject->setSOYMailTo(SOYMailConverter::SOYMAIL_MAIL);
   			$column->setColumn($columnObject);
    		$columnDao->insert($column);
    		
    		$column = new SOYInquiry_Column();
    		$column->setFormId($id);
    		$column->setLabel("件名");
    		$column->setType("SingleText");
    		$column->setRequire(false);
			$columnObject = $column->getColumn();
			$columnObject->setReplacement("#TITLE#");
   			$column->setColumn($columnObject);
    		$columnDao->insert($column);
    		
    		$column = new SOYInquiry_Column();
    		$column->setFormId($id);
    		$column->setLabel("問い合わせ内容");
    		$column->setType("MultiText");
    		$column->setRequire(true);
			$columnObject = $column->getColumn();
			$columnObject->setReplacement("#CONTENT#");
   			$column->setColumn($columnObject);
    		$columnDao->insert($column);
    		
    		$columnDao->commit();
    		
    		return $id;
    	
    	}catch(Exception $e){
    		throw $e;
    	}
    }
    
    private function setNotifyMailSubject(){
    	$config = $this->form->getConfigObject();
    	$config->setNotifyMailSubject("[SOYInquiry][".$this->form->getFormId()."]問い合わせがあります");
    	$this->form->setConfigObject($config);
    }
    
    private function setIsUseCaptcha(){
    	$config = $this->form->getConfigObject();
		$config->setIsUseCaptcha($config->enabledGD());
    	$this->form->setConfigObject($config);
    }
    
}
?>