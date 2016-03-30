<?php

class InputPage extends MobileMyPagePageBase{

	function doPost(){
		
		if(!isset($_POST["remind"]) && !soy2_check_token())$this->jump("login");
		
		$mail = $_POST["mail"];
		$mypage = MyPageLogic::getMyPage();
		$userDAO = SOY2DAOFactory::create("user.SOYShop_UserDAO");
		
		//get user
		try{
			$user = $userDAO->getByMailAddress($mail);
			
			$query = $this->mypage->createQuery($mail);
			$limit  = time() + 60 * 60 *24;
			
			$user->setAttribute("remind_query",$query);
			$user->setAttribute("remind_limit",$limit);

			$userDAO->update($user);
		}catch(Exception $e){
			//no exist user
			//@TODO check and display error_message
			$user = new SOYShop_User();
			return;
		}

		$this->send = true;
		
		//send mail
		try{
			$mailLogic = SOY2Logic::createInstance("logic.mail.MailLogic");
			$config = $mailLogic->getMyPageMailConfig("remind");
						
			SOY2::import("domain.order.SOYShop_Order");
			//convert title
			$title = $mailLogic->convertMailContent($config["title"],$user, new SOYShop_Order());

			$query = soyshop_get_mypage_url(true)."/remind/reset?q=" . $query."&f=". $mail;
			$text = "\n有効期限は" . date("Y年m月d日 H:i",$limit) . "までとなっています。\n";

			//convert content
			$mailBody = $config["header"] . "\n" . $query . "\n" . $text . "\n" . $config["footer"];
			
			$content  = $mailLogic->convertMailContent($mailBody,$user, new SOYShop_Order());
			
			$mailLogic->sendMail($mail,$title,$content);
			
			
		}catch(Exception $e){
			
		}
		
		
	}
	
	private $mypage;
	private $send = false;
	
    function InputPage() {
		$this->mypage = MyPageLogic::getMyPage();

    	WebPage::WebPage();
		if($this->mypage->getIsLoggedin())$this->jumpToTop();//ログイン済み

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
    	$this->addLink("return_link", array(
    		"link" => soyshop_get_mypage_url() . "/login"
    	));
    	
    	$this->addLink("top_link", array(
    		"link" => SOYSHOP_SITE_URL
    	));
    	
 		$userDAO = SOY2DAOFactory::create("user.SOYShop_UserDAO");
		
		try{
			$user = $userDAO->getById(1);
		}catch(Exception $e){
			$user = new SOYShop_User();
		}

    }
}
?>