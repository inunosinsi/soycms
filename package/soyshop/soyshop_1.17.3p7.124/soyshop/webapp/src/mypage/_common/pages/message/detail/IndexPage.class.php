<?php

class IndexPage extends MainMyPagePageBase{

	private $id;
	private $postDao;
	
	function doPost(){
		
		if(soy2_check_token()&&isset($_POST["Post"])){
			$post = $_POST["Post"];
						
			//入力した情報をセッションに放り込む
			$this->setPostToSession("front_message_post",$post);
			
			if(!$this->checkError($post)){
				$this->jump("message/detail/confirm/" . $this->id);
			}
		}
	}

	function __construct($args) {
		
		$this->id = $args[0];
		
		$mypage = MyPageLogic::getMyPage();
		if(!$mypage->getIsLoggedin())$this->jump("login");//ログインしていなかったら飛ばす
		$user = $this->getUser();
		
		$oldRootDir = SOY2::RootDir();
		$oldDaoDir = SOY2DAOConfig::DaoDir();
		$oldEntityDir = SOY2DAOConfig::EntityDir();
		
		$rootDir = str_replace("/soyshop/","/app/",$oldRootDir);
		$rootDir = str_replace("src/domain","message/src",$rootDir);
		
		$daoDir = str_replace("/soyshop/","/app/",$oldDaoDir);
		$daoDir = str_replace("src/domain","message/src/domain",$daoDir);
		
		SOY2::RootDir($rootDir);
		SOY2DAOConfig::DaoDir($daoDir);
		SOY2DAOConfig::EntityDir($daoDir);
		
		$this->postDao = SOY2DAOFactory::create("SOYMessage_PostDAO");
		
		WebPage::__construct();
		
		$this->addLabel("user_name", array(
			"text" => $user->getName()
		));
		
		SOY2::RootDir($rootDir);
		SOY2DAOConfig::DaoDir($oldDaoDir);
		SOY2DAOConfig::EntityDir($oldEntityDir);
		
		$this->buildDisplay($user);
		
		$this->addForm("form");
		
		$this->buildForm();
		
		//エラー周り
		DisplayPlugin::toggle("has_error",$mypage->hasError());
		$this->appendErrors($mypage);
	}
	
	function buildDisplay($user){
		try{
			$post = $this->postDao->getById($this->id);
		}catch(Exception $e){
			$post = new SOYMessage_Post();
		}
		
		$userId = $post->getUserId();
		
		//投稿した内容がそのユーザに関連したものでなければmessageトップに返す
		if($userId!=$user->getId())$this->jump("message");
		
		if(is_null($post->getOwnerName())){
			$name = $user->getName();
		}else{
			$name = $post->getOwnerName();
		}
		
		$post->setReadFlag(1);
		$this->postDao->update($post);
		
		$this->addLabel("user_name", array(
			"text" => $name
		));
		
		$this->addLabel("create_date", array(
			"text" => date("Y-m-d H:i", $post->getCreateDate())
		));
		
		$this->addLabel("tracking_number", array(
			"text" => $post->getTrackingNumber()
		));
		
		$this->addLabel("content", array(
			"html" => nl2br($post->getContent())
		));
	}
	
	function buildForm(){
		
		$post = $this->getPost();
		
		$this->addTextArea("post_content", array(
			"name" => "Post[content]",
			"value" => $post->getContent()
		));
	}
	
	function appendErrors($mypage){

		$this->createAdd("content_error", "ErrorMessageLabel", array(
			"text" => $mypage->getErrorMessage("mail_address")
		));
	}
	
	function checkError($post){
		
		$mypage = MyPageLogic::getMyPage();
		
		$res = false;
		$mypage->clearErrorMessage();
		
		//粒力がない場合
		if(strlen($post["content"]) === 0){
			$mypage->addErrorMessage("content","入力して下さい。");
			$res = true;
		}
		
		return $res;
	}
	
	function getPost(){
		$dao = $this->postDao;
		$post = $this->getPostToSession("front_message_post");
		if(is_null($post)){
			$post = new SOYMessage_Post();
		}else{
			$post = SOY2::cast("SOYMessage_Post",$post);
		}
		
		return $post;
	}
	
	function getPostToSession($key){
		$userSession = SOY2ActionSession::getUserSession();
		return $userSession->getAttribute($key);
	}
	
	function setPostToSession($key,$value){
		$userSession = SOY2ActionSession::getUserSession();
		$userSession->setAttribute($key,$value);
	}
	
	function clearPostToSession($key){
		$userSession = SOY2ActionSession::getUserSession();
		$userSession->setAttribute($key,null);
	}
}
?>