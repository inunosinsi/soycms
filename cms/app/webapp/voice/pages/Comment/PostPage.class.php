<?php

SOY2::import("domain.SOYVoice_Area");
class PostPage extends WebPage{

	private $post=null;

	function doPost(){
		
		if(soy2_check_token()&&isset($_POST["Post"])){
			
			$logic = SOY2Logic::createInstance("logic.UploadLogic");
			
			$post = $_POST["Post"];
			if($logic->checkValidate($post)){
				
				$fileName = null;
				
				if(isset($_FILES["image"]["name"]) and preg_match('/(jpg|jpeg|gif|png)$/',$_FILES["image"]["name"])){
					$fileName = $logic->uploadFile($_FILES["image"]["name"],$_FILES["image"]["tmp_name"]);
				}
				
				$dao = SOY2DAOFactory::create("SOYVoice_CommentDAO");
				$comment = SOY2::cast("SOYVoice_Comment",$post);
				$comment->setUserType(SOYVoice_Comment::TYPE_MASTER);
				$comment->setImage($fileName);
				$comment->setIsEntry(0);
				$comment->setCommentDate(time());
				$comment->setCreateDate($this->getMktime($post["date"]));
				$comment->setUpdateDate(time());
				
				try{
					$dao->insert($comment);
					CMSApplication::jump("Comment");
				}catch(Exception $e){
					
				}
			}
			
		}
		
		$this->post = $post;
	}
	
	function getMktime($date){
		return mktime($date["hour"],$date["minutes"],$date["second"],$date["month"],$date["day"],$date["year"]);
	}

    function __construct() {
    	WebPage::__construct();
    	
    	$config = $this->getConfig();
    	
    	$this->createAdd("error","HTMLModel",array(
    		"visible" => (!is_null($this->post))
    	));   	
    	
    	$this->createAdd("form","HTMLForm",array(
    		"enctype" => "multipart/form-data"
    	));
    	
    	if(!is_null($this->post)){
    		$nickname = $this->post["nickname"];
    	}else{
    		$nickname = ($config->getOwnerDisplay()==1) ? $config->getOwnerName() : null;
    	}
    	
    	$this->createAdd("nickname","HTMLInput",array(
    		"name" => "Post[nickname]",
    		"value" => $nickname
    	));
    	$this->createAdd("url","HTMLInput",array(
    		"name" => "Post[url]",
    		"value" => @$this->post["url"]
    	));
    	$this->createAdd("email","HTMLInput",array(
    		"name" => "Post[email]",
    		"value" => @$this->post["email"]
    	));
    	$this->createAdd("prefecture","HTMLSelect",array(
    		"name" => "Post[prefecture]",
    		"options" => SOYVoice_Area::getAreas(),
    		"selected" => @$this->prefecture
    	));
    	
    	$this->createAdd("content","HTMLTextArea",array(
    		"name" => "Post[content]",
    		"value" => @$this->post["content"]
    	));
    	
    	$this->buildTimeForm();
    	
    	if(!is_null($this->post)){
    		$flag = ($this->post["isPublished"]==1) ? 1 : 0;
    	}else{
    		$flag = 0;
    	}
    	
    	$this->createAdd("published","HTMLCheckBox",array(
    		"name" => "Post[isPublished]",
    		"value" => 1,
    		"selected" => $flag == 1,
    		"elementId" => "published"
    	));
    	$this->createAdd("draft","HTMLCheckBox",array(
    		"name" => "Post[isPublished]",
    		"value" => 0,
    		"selected" => $flag == 0,
    		"elementId" => "draft"
    	));
    	
    }
    
    function buildTimeForm(){
    	
    	if(!is_null($this->post)){
    		$date = $this->post["date"];
    		$year = $date["year"];
    		$month = $date["month"];
    		$day = $date["day"];
    		$hour = $date["hour"];
    		$minutes = $date["minutes"];
    		$second = $date["second"];
    	}else{
    		$year = date("Y",time());
	    	$month = date("n",time());
	    	$day = date("j",time());
	    	$hour = date("H",time());
	    	$minutes = date("i",time());
	    	$second = date("s",time());
    	}
    	
    	$this->createAdd("year","HTMLInput",array(
    		"name" => "Post[date][year]",
    		"value" => $year
    	));
    	$this->createAdd("month","HTMLInput",array(
    		"name" => "Post[date][month]",
    		"value" => $month
    	));
    	$this->createAdd("day","HTMLInput",array(
    		"name" => "Post[date][day]",
    		"value" => $day
    	));
    	$this->createAdd("hour","HTMLInput",array(
    		"name" => "Post[date][hour]",
    		"value" => $hour
    	));
    	$this->createAdd("minutes","HTMLInput",array(
    		"name" => "Post[date][minutes]",
    		"value" => $minutes
    	));
    	$this->createAdd("second","HTMLInput",array(
    		"name" => "Post[date][second]",
    		"value" => $second
    	));
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