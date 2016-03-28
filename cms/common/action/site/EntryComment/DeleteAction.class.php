<?php
/**
 * コメントの削除を行います
 */
class DeleteAction extends SOY2Action{

    function execute($request,$form,$response) {
    	
    	$logic = SOY2Logic::createInstance("logic.site.Entry.EntryCommentLogic");
    	if(!is_array($form->comment_id)) $form->comment_id = array();
    	
    	try{
    		foreach($form->comment_id as $commentId){
    			$logic->delete($commentId);
    		}
    		return SOY2Action::SUCCESS;	
    	}catch(Exception $e){
    		$this->setErrorMessage("failed","コメントの削除に失敗しました");
    		return SOY2Action::FAILED;
    	}
    }
}

class DeleteActionForm extends SOY2ActionForm{
	var $comment_id;
	
	function setComment_id($comment_id) {
		$this->comment_id = $comment_id;
	}
}
?>