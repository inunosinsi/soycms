<?php

class UpdateColumnPage extends WebPage{
	private $dao;
	private $id;
	private $post;
    function __construct($args) {
    	$id = $args[0];
    	$this->id = $id;
    	$dao = SOY2DAOFactory::create("SOYInquiry_ColumnDAO");
    	$this->dao = $dao;
    	$column = $dao->getById($id);
    	
    	$formId = $column->getFormId();
    	
    	if(isset($_POST["remove"])){
    		$dao->delete($id);    		
    	}
    	
    	if(isset($_POST["update"])){
    		$this->post = $_POST["Column"];

			$columnObject = $column->getColumn();
   			SOY2::cast($column,(object)$this->post);
   			
   			$newColumnObject = $column->getColumn();
 
    		if(!$this->SOYMailValidate()){
    			//失敗した。
    		}
    		
   			if(!$this->replacementValidate($column,$newColumnObject,$formId)) {
   				//リプレースがかぶってた！その場合もとの値を保存
   				$newColumnObject->setReplacement($columnObject->getReplacement());
   			}
  			$column->setColumn($newColumnObject);
  			
  			$dao->update($column);
    		//ハッシュ付で飛ばす
	  		CMSApplication::jump("Form.Design.Column.".$formId . "#column_" . $column->getId());
    		exit;    		
    	}
    	
    	CMSApplication::jump("Form.Design.Column.".$formId);    	
    	exit;
    }
    
    function SOYMailValidate() {
    	// TODO　フォーム内の全カラムについて、SOYMailの登録先が重複していないか、またメールアドレスの登録がされているかをチェックする
    	return true;
    }
    function replacementValidate($column,$newColumnObject,$formId) {
   		if(!$column->getRequire()){
   			//必須でない場合は空を保存
   			$newColumnObject->setReplacement("");
   		}
   		if($newColumnObject->getReplacement() == ""){
   			//空ならそのまま保存
   			return true;
   		}
   
    	//リプレース記号が重複しないか
    	
    	$columns = $this->dao->getByFormId($formId);
    	$replacement = array();
   		foreach($columns as $entity){
   			if($entity->getId() == $column->getId()) continue;
    		$columnObject = $entity->getColumn();
    		if(!isset($replacement[$columnObject->getReplacement()])) {
    			$replacement[$columnObject->getReplacement()] = "1";
    		}
    	}
    	return (!isset($replacement[$newColumnObject->getReplacement()]))?true:false;
    }
}
?>