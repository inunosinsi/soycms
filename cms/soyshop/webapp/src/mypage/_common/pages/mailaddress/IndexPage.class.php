<?php

class IndexPage extends MainMyPagePageBase{

	private $mypage;
	private $doRemind = false;
	private $send = false;

	function doPost(){

		if(soy2_check_token() && soy2_check_referer()){

			//メールの入力がなかった場合は処理を停止する
			if(!isset($_POST["mail"])){
				return false;
			}

			$newMailAddress = trim($_POST["mail"]);
			$mypage = $this->getMyPage();

			$user = $mypage->getUser();

			$mailTokenDao = SOY2DAOFactory::create("user.SOYShop_MailAddressTokenDAO");
			$tokenObj = new SOYShop_MailAddressToken();
			$tokenObj->setUserId($user->getId());
			$tokenObj->setNew($newMailAddress);
			$tokenObj->setToken($mypage->createQuery($newMailAddress));
			$tokenObj->setLimit(time() + 60 * 60 * 24);

			//send mail
			$mailLogic = SOY2Logic::createInstance("logic.mail.MailLogic");
			$config = $mailLogic->getMyPageMailConfig("edit");

			SOY2::import("domain.order.SOYShop_Order");
			//convert title
			$title = $mailLogic->convertMailContent($config["title"], $user, new SOYShop_Order());

			$query = soyshop_get_mypage_url(true) . "/mailaddress/change?q=" . $tokenObj->getToken();
			$text = "\n". MessageManager::get("MYPAGE_LIMIT_TERM_CONTENT", array("limit" => date("Y年m月d日 H:i", $tokenObj->getLimit()))) . "\n";

			//convert content
			$mailBody = $config["header"] . "\n" . $query . "\n" . $text . "\n" . $config["footer"];
			$content  = $mailLogic->convertMailContent($mailBody,$user, new SOYShop_Order());

			$order = new SOYShop_Order();
			$order->setUserId($user->getId());

			try{
				$mailTokenDao->insert($tokenObj);
				$mailLogic->sendMail($newMailAddress, $title, $content, "", $order);
				$this->send = true;
			}catch(Exception $e){
				//@ToDo エラーログ
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
		$this->checkIsLoggedIn(); //ログインチェック
		$mypage = $this->getMyPage();

		parent::__construct();

    	$this->addForm("form");

    	// before remind mail
    	$this->addModel("before", array(
    		"visible" => (!$this->send)
    	));
    	$this->addInput("mailaddress", array(
    		"name" => "mail",
    		"value" => $mypage->getUser()->getMailAddress(),
			"attr:required" => "required"
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
