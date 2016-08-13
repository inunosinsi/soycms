<?php

class PublishLogic extends SOY2LogicBase{

	function insertVoice($value){
		
		if($this->checkValidate($value)){
			
			$dao = SOY2DAOFactory::create("SOYVoice_CommentDAO");
			$obj = SOY2::cast("SOYVoice_Comment",$value);	
			
			$obj->setUserType(SOYVoice_Comment::TYPE_CUSTOMER);
			$obj->setIsPublished(1);
			$obj->setIsEntry(0);
			$obj->setCommentDate(time());
			$obj->setCreateDate(time());
			$obj->setUpdateDate(time());
			
			$logic = SOY2Logic::createInstance("logic.UploadLogic");
			if(isset($_FILES["image"]["name"]) and preg_match('/(jpg|jpeg|gif|png)$/',$_FILES["image"]["name"])){
				$fileName = $logic->uploadFile($_FILES["image"]["name"],$_FILES["image"]["tmp_name"]);
				$obj->setImage($fileName);
			}
			
			try{
				$id = $dao->insert($obj);
				$res = true;
			}catch(Exception $e){
				return false;
			}
			
			$config = $this->getConfig();
			if($config->getIsSync()==1){
				$obj->setId($id);
				$logic = SOY2Logic::createInstance("logic.SyncLogic");
				$res = $logic->syncPublic($obj);
			}
			
			return $res;
			
		}

		return false;
		
	}
	
	function checkValidate($value){
		
		$res = true;
		
		//ニックネームチェック
		if(isset($value["nickname"])){
			if(strlen($value["nickname"])==0){
				$res = false;
			}
			
			if(preg_match('/<script.*>/',$value["nickname"],$tmp)){
				$res = false;
			}
		}
		
		//コメントチェック
		if(isset($value["content"])){
			if(strlen($value["content"])==0){
				$res = false;
			}
			
			if(preg_match('/<script.*>/',$value["content"],$tmp)){
				$res = false;
			}
		}
		
		//URLチェック
		if(isset($value["url"])&&strlen($value["url"])>0){
			if(!preg_match('/^(https?|ftp)(:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)$/',$value["url"])){
				$res = false;
			}
		}
		
		//メールアドレスチェック
		if(isset($value["email"])&&strlen($value["email"])>0){
			if(!preg_match('/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/',$value["email"])){
				$res = false;
			}
		}
		
		return $res;
		
	}

	function getVoices(){
		$dao = SOY2DAOFactory::create("SOYVoice_CommentDAO");
		$config = $this->getConfig();
		$count = $config->getCount();
		
		$dao->setLimit($count);
		
		try{
			$voices = $dao->getCommentIsPublished();
		}catch(Exception $e){
			$voices = array();
		}
		
		return $voices;
	}

	function getConfig(){
		$dao = SOY2DAOFactory::create("SOYVoice_ConfigDAO");
		try{
			$config = $dao->getById(1);
		}catch(Exception $e){
			$config = new SOYVoice_Config();
			$config->setCount(5);
		}
		
		return $config;
	}
}
?>