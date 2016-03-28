<?php
/**
 * 認証状態の変更
 */
class ToggleApprovedAction extends SOY2Action{

    function execute($request,$form,$response) {
    	$logic = SOY2Logic::createInstance("logic.site.Entry.EntryCommentLogic");
    	if(!is_array($form->comment_id)) $form->comment_id = array();
    	try{
    		foreach($form->comment_id as $commentId){
    			$logic->toggleApproved($commentId,$form->state);
    		}
    		$this->setAttribute("new_state",$form->state);
    		return SOY2Action::SUCCESS;	
    	}catch(Exception $e){
    		$this->setErrorMessage("failed","コメントの認証状態を変更できませんでした。");
    		return SOY2Action::FAILED;
    	}
    }
}

class ToggleApprovedActionForm extends SOY2ActionForm{
	var $comment_id;
	var $state;
	

	/**
	 * @validator number {"require":true}
	 */
	function setComment_id($comment_id) {
		$this->comment_id = $comment_id;
	}
	
	/**
	 * @validator number {"min":0,"max":1}
	 */
	function setState($state) {
		$this->state = $state;
	}
}

?>