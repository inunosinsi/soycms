<?php

class CopyPage extends WebPage{
	
	private $dao;
	private $form;
	private $errorMessage="";
	
	function doPost(){
		$form = $this->dao->getById($_POST["copy_from"]);
		
		$this->form = SOY2::cast($form,(object)$_POST["Form"]);
		
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
				
				$id = $this->dao->insert($form);
				
				//フォーム複製後
				$columnDao = SOY2DAOFactory::create("SOYInquiry_ColumnDAO");
				$columns = $columnDao->getByFormId($_POST["copy_from"]);
				
				foreach($columns as $column){
					$column->setFormId($id);
					$columnDao->insert($column);
				}
				
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
    	
    	$this->addSelect("form_list", array(
    		"options" => $this->getFormList(),
    		"name" => "copy_from"
    	));
    	
    	$this->addInput("form_name", array(
    		"name" => "Form[name]",
    		"value" => $this->form->getName()
    	));
    	
    	$this->addInput("form_id", array(
    		"name" => "Form[formId]",
    		"value" => $this->form->getFormId()
    	));
    	
    	$this->addForm("create_form");
    	
    	$this->addLabel("error", array(
    		"html" => $this->errorMessage,
    		"visible" => strlen((string)$this->errorMessage)
    	));
    }
    
    function getFormList(){
    	$forms = $this->dao->get();
    	
    	$res = array();
    	foreach($forms as $form){
    		$res[$form->getId()] = $form->getName();
    	}
    	
    	return $res;
    }
}
?>