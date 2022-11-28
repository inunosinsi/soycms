<?php

class CreatePage extends WebPage{
	
	var $dao;
	var $form;
	var $errorMessage;
	
	function doPost(){
		$form = $_POST["Form"];
		
		$this->form = SOY2::cast("SOYInquiry_Form",(object)$form);
		
		$failed= false;
		
		//エラーチェック
		$formId = $this->form->getFormId();
		if(strlen($formId) < 0 || strlen($formId) > 512
			|| !preg_match('/^[a-zA-Z_0-9]+$/',$formId)
		){
			$this->errorMessage = '<p class="error">フォームIDは半角英数字のみ指定可能です。</p>';
			$failed = true;
		}
		
		try{
			$this->dao->getByFormId($formId);
			$this->errorMessage = '<p class="error">指定のフォームIDは既に使われています。</p>';
			$failed = true;
		}catch(Exception $e){
			//do nothing
		}
		
		try{
			if(!$failed){
				
				$logic = SOY2Logic::createInstance("logic.FormLogic");
				$logic->createForm($this->form);
				
				CMSApplication::jump("Form");
			}
		}catch(Exception $e){
			$this->errorMessage = '<p class="error">登録に失敗しました</p>';
		}

	}
	
	function prepare(){
		$this->dao = SOY2DAOFactory::create("SOYInquiry_FormDAO");
    	$this->form = new SOYInquiry_Form();
    	
    	parent::prepare();
	}

    function __construct() {
    	    	 
    	parent::__construct();
    	
    	$this->createAdd("form_name","HTMLInput",array(
    		"name" => "Form[name]",
    		"value" => $this->form->getName()
    	));
    	
    	$this->createAdd("form_id","HTMLInput",array(
    		"name" => "Form[formId]",
    		"value" => $this->form->getFormId()
    	));
    	
    	$this->createAdd("create_form","HTMLForm");
    	
    	$this->createAdd("error","HTMLLabel",array(
    		"html" => $this->errorMessage,
    		"visible" => strlen($this->errorMessage)
    	));
    }
}
?>