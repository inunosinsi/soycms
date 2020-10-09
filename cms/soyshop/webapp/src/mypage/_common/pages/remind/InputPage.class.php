<?php

class InputPage extends MainMyPagePageBase{

	private $mypage;
	private $doRemind = false;
	private $send = false;

	function doPost(){

		if(soy2_check_token() && soy2_check_referer()){

			//メールの入力がなかった場合は処理を停止する
			if(!isset($_POST["mail"])){
				return false;
			}

			$mail = $_POST["mail"];
			$mypage = $this->getMyPage();
			$userDAO = SOY2DAOFactory::create("user.SOYShop_UserDAO");

			//get user
			try{
				$user = $userDAO->getByMailAddress($mail);

				$query = $this->mypage->createQuery($mail);
				$limit  = time() + 60 * 60 * 24;

				$user->setAttribute("remind_query", $query);
				$user->setAttribute("remind_limit", $limit);

				$userDAO->update($user);
				$this->doRemind = true;
			}catch(Exception $e){
				//
			}

			if($this->doRemind){
				//send mail
				$mailLogic = SOY2Logic::createInstance("logic.mail.MailLogic");
				$config = $mailLogic->getMyPageMailConfig("remind");

				SOY2::import("domain.order.SOYShop_Order");
				//convert title
				$title = $mailLogic->convertMailContent($config["title"], $user, new SOYShop_Order());

				$query = soyshop_get_mypage_url(true) . "/remind/reset?q=" . $query . "&f=" . rawurlencode($mail);
				$text = "\n". MessageManager::get("MYPAGE_LIMIT_TERM_CONTENT", array("limit" => date("Y年m月d日 H:i", $limit))) . "\n";

				//convert content
				$mailBody = $config["header"] . "\n" . $query . "\n" . $text . "\n" . $config["footer"];
				$content  = $mailLogic->convertMailContent($mailBody,$user, new SOYShop_Order());

				try{
					$mailLogic->sendMail($mail, $title, $content);
					$this->send = true;
				}catch(Exception $e){
					//@ToDo エラーログ
				}
			}


			//リマインドメール送信後に他の場所を遷移する
			if(isset($_GET["r"])){
				$param = soyshop_remove_get_value($_GET["r"]);
				if($this->send){
					soyshop_redirect_designated_page($param, "send=complete");
				}else{
					soyshop_redirect_designated_page($param, "send=error");
				}
			}
		}
	}

    function __construct() {
		$this->mypage = $this->getMyPage();

    	//ログイン済み
		if($this->mypage->getIsLoggedin()){
			$this->jumpToTop();
		}

		parent::__construct();

    	$this->addForm("form");

    	// before remind mail
    	$this->addModel("before", array(
    		"visible" => (!$this->send)
    	));
    	$this->addInput("mail", array(
    		"name" => "mail",
    		"value" => ""
    	));

    	// afrer remind mail
    	$this->addModel("after", array(
    		"visible" => ($this->send)
    	));

		$this->addLink("login_link", array(
			"link" => soyshop_get_mypage_url() . "/login"
		));

    	$this->addLink("top_link", array(
    		"link" => soyshop_get_site_url()
    	));
    }
}
