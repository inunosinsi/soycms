<?php
SOY2HTMLFactory::importWebPage("message.detail.IndexPage");
class ConfirmPage extends IndexPage{

	private $id;

	function doPost(){

		if(soy2_check_token()){

			if(isset($_POST["post"])){
				$mypage = MyPageLogic::getMyPage();
				$user = $this->getUser();

				$post = $this->getPost("front_message_post");

				$post->setUserId($user->getId());

				//念の為にownerNameをnullにしておく
				$post->setOwnerName(null);

				//どのポストに返答したか？
				$post->setReplyId($this->id);

				$postDao = $this->postDao;
				try{
					$postDao->insert($post);
					SOY2::import("domain.config.SOYShop_ServerConfig");
					$serverConf = SOYShop_ServerConfig::load();
					$administratorMail = $serverConf->getAdministratorMailAddress();

					$this->send($administratorMail,$post);
					$this->jump("message/detail/complete/" . $this->id);
				}catch(Exception $e){
					var_dump($e);
				}
			}


			if(isset($_POST["back"])){
				$this->jump("message/detail/" . $this->id);
			}
		}

	}

	function __construct($args) {
		$this->checkIsLoggedIn(); //ログインチェック
		$this->id = $args[0];

		$user = $this->getUser();

		$oldDaoDir = SOY2DAOConfig::DaoDir();
		$oldEntityDir = SOY2DAOConfig::EntityDir();

		$daoDir = str_replace("/soyshop/","/app/",$oldDaoDir);
		$daoDir = str_replace("src/domain","message/src/domain",$daoDir);

		SOY2DAOConfig::DaoDir($daoDir);
		SOY2DAOConfig::EntityDir($daoDir);

		$this->postDao = SOY2DAOFactory::create("SOYMessage_PostDAO");

		parent::__construct();

		$this->addLabel("user_name", array(
			"text" => $user->getName()
		));

		$this->addForm("form");

		$this->buildForm();

		SOY2DAOConfig::DaoDir($oldDaoDir);
		SOY2DAOConfig::EntityDir($oldEntityDir);
	}

	function send($to,$post){

		//管理者向け
		$user = $this->getUser();
    	$title = "新しい質問があります ".date("Y-m-d H:i:s");
    	$content = $user->getName() . "様から新しい質問が届きました。 " .
    			"本文：" . $post->getContent();

		$mailLogic = SOY2Logic::createInstance("logic.mail.MailLogic");

    	$serverConfig = SOYShop_ServerConfig::load();
		$mailLogic->sendMail($to,$title,$content,"テストメール送信先");

		//利用者向け
		$title = "ご質問を登録しました";
    	$content = $user->getName() . "様 " .
    			"ご利用ありがとうございます。以下のご質問を登録しました。本文：" . $post->getContent();

		$mailLogic->sendMail($user->getMailaddress(),$title,$content,"テストメール送信先");

    }
}
?>
