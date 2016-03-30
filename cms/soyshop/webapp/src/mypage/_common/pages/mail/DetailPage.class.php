<?php
class DetailPage extends MainMyPagePageBase{
	
	private $id;
	private $user;
	
	function doPost(){

	}
	
	function DetailPage($args){
		
		$mypage = MyPageLogic::getMyPage();
		
		//ログインチェック
		if(!$mypage->getIsLoggedin()){
			$this->jump("login");
		}
		
		//IDが指定していない場合は受信メール一覧に飛ばす
		if(!isset($args[0])){
			$this->jump("mail");
		}
		
		WebPage::WebPage();
		
		$this->user = $this->getUser();
		
		$this->addLabel("user_name", array(
			"text" => $this->user->getName()
		));
		
		$this->id = (int)$args[0];
		
		$this->buildDetail();		
	}
	
	function buildDetail(){
		
		$mailLogDao = SOY2DAOFactory::create("logging.SOYShop_MailLogDAO");
		try{
			$log = $mailLogDao->getByIdAndUserId($this->id, $this->user->getId());
		}catch(Exception $e){
			$this->jump("mail");
		}
		
		//お客様宛てのメールではない場合はメール一覧に飛ばす
		if(is_null($log->getUserId())){
			$this->jump("mail");
		}
		
		//違うお客様宛に送信したメールの場合もメール一覧に飛ばす
		if($log->getUserId() != $this->user->getId()){
			$this->jump("mail");
		}
		
		$this->addLabel("send_date", array(
			"text" => date("Y年m月d日 H:i", $log->getSendDate())
		));
		
		$this->addLabel("title", array(
			"text" => $log->getTitle()
		));
		
		$this->addLabel("content", array(
			"html" => nl2br($log->getContent())
		));
		
		$this->addLink("top_link", array(
    		"link" => soyshop_get_mypage_top_url()
    	));			
	}
}
?>