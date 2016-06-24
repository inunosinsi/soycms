<?php

class CommentUpdateAction extends SOY2Action{

	private $commentId;

    function execute($req) {
    	$dao = SOY2DAOFactory::create("cms.EntryCommentDAO");
    	
    	try{
    		$comment = $dao->getById($this->commentId);
    		
    		$title = $req->getParameter("title");
    		if(isset($title)){
    			$comment->setTitle($title);
    		}
    		
    		$author = $req->getParameter("author");
    		if(isset($author)){
    			$comment->setAuthor($author);
    		}
    		
    		$body = $req->getParameter("content");
    		if(isset($body)){
    			$comment->setBody($body);
    		}
    		
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