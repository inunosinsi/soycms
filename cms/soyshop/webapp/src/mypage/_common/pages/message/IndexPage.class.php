<?php

class IndexPage extends MainMyPagePageBase{

	private $postDao;

	function doPost(){

		if(soy2_check_token()&&isset($_POST["Post"])){
			$post = $_POST["Post"];

			//入力した情報をセッションに放り込む
			$this->setPostToSession("front_message_post",$post);

			if(!$this->checkError($post)){
				$this->jump("message/confirm");
			}
		}
	}

	function __construct() {
		$this->checkIsLoggedIn(); //ログインチェック

		$mypage = $this->getMyPage();
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

		parent::__construct();

		$this->addLabel("user_name", array(
			"text" => $user->getName()
		));

		$this->addForm("form");

		$this->buildForm();

		//userにひもづいた投稿と回答を取得
		$messages = $this->getMessagesByUserId($user->getId());

		SOY2::RootDir($rootDir);
		SOY2DAOConfig::DaoDir($oldDaoDir);
		SOY2DAOConfig::EntityDir($oldEntityDir);

		$this->createAdd("message_list","MessageListComponent", array(
			"list" => $messages
		));


		//エラー周り
		DisplayPlugin::toggle("has_error",$mypage->hasError());
		$this->appendErrors($mypage);
	}

	function buildForm(){

		$post = $this->getPost();

		$this->addTextArea("content", array(
			"name" => "Post[content]",
			"value" => $post->getContent()
		));
	}

	function appendErrors(MyPageLogic $mypage){

		$this->createAdd("content_error", "ErrorMessageLabel", array(
			"text" => $mypage->getErrorMessage("mail_address")
		));
	}

	function checkError($post){

		$mypage = $this->getMyPage();

		$res = false;
		$mypage->clearErrorMessage();

		//粒力がない場合
		if(strlen($post["content"]) === 0){
			$mypage->addErrorMessage("content","入力して下さい。");
			$res = true;
		}

		$mypage->save();

		return $res;
	}

	function getMessagesByUserId($userId){
		if(!$this->postDao){
			$this->postDao = SOY2DAOFactory::create("SOYMessage_PostDAO");
		}

		try{
			$messages = $this->postDao->getByUserId($userId);
		}catch(Exception $e){
			$messages = array();
		}
		return $messages;
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

class MessageListComponent extends HTMLList{

	protected function populateItem($entity, $key) {

		$this->addModel("is_owner", array(
			"visible" => (!is_null($entity->getOwnerName()))
		));

		$this->addLabel("tracking_number", array(
			"text" => $entity->getTrackingNumber()
		));

		$this->addLabel("create_date", array(
			"text" => date("Y-m-d H:i", $entity->getCreateDate())
		));

		$this->addLabel("content", array(
			"text" => mb_substr($entity->getContent(),0,60)
		));

		$this->addLink("detail_link", array(
			"link" => soyshop_get_mypage_url() ."/message/detail/" . $entity->getId(),
			"text" => ($entity->getReadFlag()==1) ? "既読" : "未読"
		));
	}
}
