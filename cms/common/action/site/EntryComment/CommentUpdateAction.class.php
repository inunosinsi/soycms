<?php

class CommentUpdateAction extends SOY2Action{

	private $commentId;

    function execute($req) {
    	$dao = SOY2DAOFactory::create("cms.EntryCommentDAO");
    	
    	try{
    		$body = $req->getParameter("content");	
    		
    		$comment = $dao->getById($this->commentId);
    		$comment->setBody($body);
    		
    		$dao->update($comment);
    	}catch(Exception $e){
    		return SOY2Action::FAILED;
    	}
    	
    	return SOY2Action::SUCCESS;
    	
    }

    function getCommentId() {
    	return $this->commentId;
    }
    function setCommentId($commentId) {
    	$this->commentId = $commentId;
    }
}
?>