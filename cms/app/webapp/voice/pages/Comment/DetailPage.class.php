<?php

SOY2::import("domain.SOYVoice_Area");
class DetailPage extends WebPage{

	private $id;
	private $post=null;

	function doPost(){
		
		if(soy2_check_token()){
		
			$dao = SOY2DAOFactory::create("SOYVoice_CommentDAO");
			$comment = $dao->getById($this->id);
		
			$logic = SOY2Logic::createInstance("logic.UploadLogic");
		
			if(isset($_POST["Detail"])){
				
				$post = SOY2::cast($comment,$_POST["Detail"]);
				$post->setCommentDate($this->getMktime($_POST["Detail"]["date"]));
				if($logic->checkValidate($_POST["Detail"])){
					
					$fileName = $_POST["Detail"]["imagePath"];
					
					if(isset($_POST["delete"])&&$_POST["delete"]==1){
						$fileName = null;
					}
					
					if(isset($_FILES["image"]["name"]) and preg_match('/(jpg|jpeg|gif|png)$/',$_FILES["image"]["name"])){
						$fileName = $logic->uploadFile($_FILES["image"]["name"],$_FILES["image"]["tmp_name"]);
					}
					
					$post->setImage($fileName);
					$post->setUpdateDate(time());
					
					try{
						$dao->update($post);
						CMSApplication::jump("Comment.Detail.".$this->id);
					}catch(Exception $e){
						
					}
				}
				
				$this->post = $post;
			}
			
			if(isset($_POST["reply_delete"])){
				$comment->setReply(null);
				
				try{
					$dao->update($comment);
				}catch(Exception $e){
				
				}
				CMSApplication::jump("Comment.Detail.".$this->id);
			}
			
			if(isset($_POST["Reply"])){
				$reply = $_POST["Reply"];
				$reply["date"] = time();
				$comment->setReply(soy2_serialize($reply));
				
				try{
					$dao->update($comment);
					CMSApplication::jump("Comment.Detail.".$this->id);
				}catch(Exception $e){
				
				}
			}
		}
	}
	
	function getMktime($date){
		return mktime($date["hour"],$date["minutes"],$date["second"],$date["month"],$date["day"],$date["year"]);
	}

    function __construct($args) {
    	$this->id = $args[0];
    	
    	WebPage::__construct();
    	
    	if(is_null($this->post)){
    		$comment = $this->getComment();
    	}else{
    		$comment = $this->post;
    	}
    	
    	if(!$comment)CMSApplication::jump("Comment");
    	
    	$this->createAdd("error","HTMLModel",array(
    		"visible" => (!is_null($this->post))
    	));
    	
    	$this->createAdd("form","HTMLForm",array(
    		"enctype" => "multipart/form-data"
    	));
    	
    	$this->createAdd("id","HTMLLabel",array(
			"text" => $comment->getId()
		));
    	
    	$this->createAdd("nickname","HTMLInput",array(
    		"name" => "Detail[nickname]",
    		"value" => $comment->getNickname()
    	));
    	$this->createAdd("url","HTMLInput",array(
    		"name" => "Detail[url]",
    		"value" => $comment->getUrl()
    	));
    	$this->createAdd("email","HTMLInput",array(
    		"name" => "Detail[email]",
    		"value" => $comment->getEmail()
    	));
    	$this->createAdd("prefecture","HTMLSelect",array(
    		"name" => "Detail[prefecture]",
    		"options" => SOYVoice_Area::getAreas(),
    		"selected" => @$comment->getPrefecture()
    	));
    	
    	$this->createAdd("is_image","HTMLModel",array(
    		"visible" => (strlen($comment->getImage()))
    	));
    	
    	$this->createAdd("image","HTMLImage",array(
    		"src" => SOY_VOICE_IMAGE_ACCESS_PATH.$comment->getImage()
    	));
    	$this->createAdd("delete","HTMLCheckBox",array(
    		"name" => "delete",
    		"value" => 1,
    		"elementId" => "delete"
    	));
    	$this->createAdd("image_path","HTMLInput",array(
    		"name" => "Detail[imagePath]",
    		"value" => $comment->getImage()
    	));
    	
    	$this->createAdd("content","HTMLTextArea",array(
    		"name" => "Detail[content]",
    		"value" => $comment->getContent()
    	));
    	
    	$this->buildTimeForm($comment);
    	
    	$this->createAdd("published","HTMLCheckBox",array(
    		"name" => "Detail[isPublished]",
    		"value" => 1,
    		"selected" => $comment->getIsPublished() == 1,
    		"elementId" => "published"
    	));
    	$this->createAdd("draft","HTMLCheckBox",array(
    		"name" => "Detail[isPublished]",
    		"value" => 0,
    		"selected" => $comment->getIsPublished() == 0,
    		"elementId" => "draft"
    	));
    	
    	$this->createAdd("is_entry","HTMLLabel",array(
			"text" => ($comment->getIsEntry()==1) ? "済" : "未"
		));
    	
    	$this->buildReplyForm($comment);
    }
    
    function buildTimeForm($comment){
    	
    	$time = $comment->getCreateDate();
	
    	$year = date("Y",$time);
	    $month = date("n",$time);
	    $day = date("j",$time);
	    $hour = date("H",$time);
	    $minutes = date("i",$time);
	    $second = date("s",$time);
    	
    	$this->createAdd("year","HTMLInput",array(
    		"name" => "Detail[date][year]",
    		"value" => $year
    	));
    	$this->createAdd("month","HTMLInput",array(
    		"name" => "Detail[date][month]",
    		"value" => $month
    	));
    	$this->createAdd("day","HTMLInput",array(
    		"name" => "Detail[date][day]",
    		"value" => $day
    	));
    	$this->createAdd("hour","HTMLInput",array(
    		"name" => "Detail[date][hour]",
    		"value" => $hour
    	));
    	$this->createAdd("minutes","HTMLInput",array(
    		"name" => "Detail[date][minutes]",
    		"value" => $minutes
    	));
    	$this->createAdd("second","HTMLInput",array(
    		"name" => "Detail[date][second]",
    		"value" => $second
    	));
    }
    function buildReplyForm($comment){
    	
    	$config = $this->getConfig();
    	
    	$reply = soy2_unserialize($comment->getReply());
    	
    	if(is_array($reply)){
    		$nickname = $reply["author"];
    	}else{
    		$nickname = ($config->getOwnerDisplay()==1) ? $config->getOwnerName() : null;
    	}
    	$this->createAdd("reply_form","HTMLForm");
    	
    	$this->createAdd("reply_author","HTMLInput",array(
    		"name" => "Reply[author]",
    		"value" => $nickname
    	));
    	$this->createAdd("reply_content","HTMLTextArea",array(
    		"name" => "Reply[content]",
    		"value" => (isset($reply["content"])) ? $reply["content"] : null
    	));
    	
    	$this->createAdd("is_reply","HTMLModel",array(
    		"visible" => (is_array($reply))
    	));
    }
    
    function getComment(){
    	$dao = SOY2DAOFactory::create("SOYVoice_CommentDAO");
    	try{
    		return $dao->getById($this->id);
    	}catch(Exception $e){
    		return false;
    	}
    }
    
    function getConfig(){
    	
    	$dao = SOY2DAOFactory::create("SOYVoice_ConfigDAO");
    	try{
    		$config = $dao->getById(1);
    	}catch(Exception $e){
    		$config = new SOYVoice_Config();
    	}
    	
    	return $config;
    }
}
?>