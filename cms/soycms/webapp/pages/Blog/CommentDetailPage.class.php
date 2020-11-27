<?php

class CommentDetailPage extends CMSWebPageBase{

	function doPost(){
		if(soy2_check_token()){
			$result = $this->run("EntryComment.CommentUpdateAction",array(
				"commentId"=>$this->id
			));
		}

		$this->jump("Blog.CommentDetail.".$this->id);
	}

	var $id;


	function __construct($arg) {
		$commentId = @$arg[0];
		$this->id = $commentId;

		//記事公開管理者権限が必要
		if(!UserInfoUtil::hasEntryPublisherRole()){
			echo CMSMessageManager::get("SOYCMS_ERROR");
			exit;
		}

		$result = $this->run("EntryComment.CommentDetailAction",array("commentId"=>$commentId));

		if(!$result->success()){
			echo CMSMessageManager::get("SOYCMS_ERROR");
			exit;
		}

		parent::__construct();
		$comment = $result->getAttribute("entity");

		$result = $this->run("Entry.EntryDetailAction",array("id"=>$comment->getEntryId()));

		if(!$result->success()){
			echo CMSMessageManager::get("SOYCMS_ERROR");
			exit;
		}

		$entry = $result->getAttribute("Entry");
		$title = $comment->getTitle();

		$author = $comment->getAuthor();

		if(strlen($comment->getMailAddress()) != 0){
			$author .= "(".$comment->getMailAddress().")";
		}

		if(strlen($title) == 0){
			$title = CMSMessageManager::get("SOYCMS_NO_TITLE");
		}

		$this->addLabel("title", array(
			"text" => $title
		));

		$this->addLabel("author", array(
			"text" => $author
		));

		$this->addLabel("entry_title", array(
			"text" => $entry->getTitle()
		));

		$this->addLabel("submit_date", array(
			"text" => (is_numeric($comment->getSubmitDate())) ? date("Y-m-d H:i:s",$comment->getSubmitDate()) : ""
		));

		$this->addLabel("state", array(
			"text" => ($comment->getIsApproved() == 0) ? CMSMessageManager::get("SOYCMS_DENY") : CMSMessageManager::get("SOYCMS_ALLOW")
		));

		$this->addLabel("content", array(
			"text" => $comment->getBody()
		));

		$this->addForm("comment_form");

		$this->addTextArea("content_edit", array(
			"text"=>$comment->getBody(),
			"name" => "content"
		));
	}
}
