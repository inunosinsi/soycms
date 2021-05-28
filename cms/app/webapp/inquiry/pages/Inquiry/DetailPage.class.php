<?php

class DetailPage extends WebPage{

	var $id;
    var $form;

	function doPost(){

		if(soy2_check_token()){
			$commenDAO = SOY2DAOFactory::create("SOYInquiry_CommentDAO");

			if(isset($_POST["add_comment"])){

				$comment = SOY2::cast("SOYInquiry_Comment",(object)$_POST["Memo"]);
				$comment->setInquiryId($this->id);

				$comment->setContent(htmlspecialchars($comment->getContent()));

				try{
					$commenDAO->insert($comment);
				}catch(Exception $e){
					//
				}

				CMSApplication::jump("Inquiry.Detail." . $this->id . "#comment");
			}

			//お問い合わせへの返信
			if(isset($_POST["reply"])){

				$to = trim($_POST["Reply"]["to"]);
				$subject = trim($_POST["Reply"]["subject"]);
				$from = trim($_POST["Reply"]["from"]);
				$content = htmlspecialchars($_POST["Reply"]["content"], ENT_QUOTES, "UTF-8");
				$cc = (isset($_POST["Reply"]["cc"]) && strlen($_POST["Reply"]["cc"])) ? trim($_POST["Reply"]["cc"]) : null;

				//メールを送信
				$mailLogic = SOY2Logic::createInstance("logic.MailLogic", array(
					"serverConfig" => SOY2DAOFactory::create("SOYInquiry_ServerConfigDAO")->get()
				));
				$mailLogic->prepareSend();
				if(isset($cc)) $mailLogic->getSend()->addRecipient($cc);
				$mailLogic->getSend()->setFrom($from, null);	//fromの入れ替え

				$mailLogic->sendMail($to, $subject, $content, null);

                //CCがある場合はcc宛にもメールする
                if(isset($cc) && strlen($cc)){
                    $mailLogic->sendMail($cc, $subject, $content, null);
                }

                //管理者に確認メールを送信する
                if($this->form->getConfigObject()->getIsSendNotifyMail()) {
                    $serverConfig = SOY2DAOFactory::create("SOYInquiry_ServerConfigDAO")->get();
                    $adminFrom = $serverConfig->getAdministratorMailAddress();
                    if($adminFrom != $cc){
                        $mailLogic->sendMail($adminFrom, $subject, $content, null);
                    }
                }

				$comment = new SOYInquiry_Comment();
				$comment->setInquiryId($this->id);

				//author
				$session = SOY2ActionSession::getUserSession();
				$author = $session->getAttribute("username");
				if(is_null($author)){
					$author = $session->getAttribute("loginid");
				}
				$comment->setAuthor($author);
				$comment->setTitle($subject);

				$body = "to  :" . $to . "\n";
				$body .= "from:" . $from . "\n";
				if(isset($cc)) $body .= "cc  :" . $cc . "\n\n";
				$body .= "メール本文:\n" . $content;

				$comment->setContent($body);

				try{
					$commenDAO->insert($comment);
				}catch(Exception $e){
					//
				}

				CMSApplication::jump("Inquiry.Detail." . $this->id . "#comment");
			}
		}
	}

	function __construct($args) {
		if(!isset($args[0]) || !is_numeric($args[0])) CMSApplication::jump("Inquiry");
		$this->id = (int)$args[0];

		$dao = SOY2DAOFactory::create("SOYInquiry_InquiryDAO");
		$formDao = SOY2DAOFactory::create("SOYInquiry_FormDAO");

		try{
			$inquiry = $dao->getById($this->id);
		}catch(Exception $e){
			CMSApplication::jump("Inquiry");
		}

		try{
			$form = $formDao->getById($inquiry->getFormId());
		}catch(Exception $e){
			$form = new SOYInquiry_Form();
		}

        $this->form = $form;

        parent::__construct();

        try{
            //未読の場合
			if($inquiry->isUnread()){
				$dao->setReaded($inquiry->getId());
			}
        }catch(Exception $e){
            //
        }

		$this->addLabel("create_date", array(
			"text" => date("Y-m-d H:i:s", $inquiry->getCreateDate())
		));
		$this->addLabel("tracking_number", array(
			"text" => $inquiry->getTrackingNumber()
		));
 		$this->addLabel("inquiry_id", array(
			"text" => $inquiry->getId()
		));

		$this->addLabel("form_name", array(
			"text" => (strlen($form->getName())) ? $form->getName() : "-"
		));

		$this->addLabel("ip_address", array(
			"text" => $inquiry->getIpAddress()
		));

		$this->addLabel("content", array(
			"html" => SOYInquiryUtil::shapeInquiryContent($inquiry->getContent())
		));

		//記事のリンク
		$blogEntryUrl = SOYInquiryUtil::getBlogEntryUrlByInquiryId($inquiry->getId());
		DisplayPlugin::toggle("blog_entry_url", strlen($blogEntryUrl));
		$this->addLink("blog_entry_link", array(
			"link" => $blogEntryUrl
		));

		//コメントを取得
		$commenDAO = SOY2DAOFactory::create("SOYInquiry_CommentDAO");
		$comments = $commenDAO->getByInquiryId($this->id);

		DisplayPlugin::toggle("comment", count($comments));
		$this->createAdd("comment_list", "_common.CommentListComponent", array(
			"list" => $comments
		));

		//コメントフォーム
		$this->addForm("comment_form");

		$this->addInput("memo_title", array(
			"name" => "Memo[title]",
			"value" => ""
		));

		$this->addInput("memo_name", array(
			"name" => "Memo[author]",
			"value" => SOY2ActionSession::getUserSession()->getAttribute("username")
		));

		$this->addTextArea("memo_content", array(
			"name" => "Memo[content]",
			"value" => ""
		));

		//返信フォーム
		$mailAddress = self::getMailAddressByInquiry($inquiry);

		DisplayPlugin::toggle("reply_mail_area", (strlen($mailAddress)));

		$this->addForm("reply_form");

		$this->addInput("to", array(
			"name" => "Reply[to]",
			"value" => $mailAddress,
			"readonly" => true,
			"attr:required" => "required"
		));

		list($from, $cc) = self::getFromAndCc();
		$this->addInput("from", array(
			"name" => "Reply[from]",
			"value" => $from,
			"readonly" => true,
			"attr:required" => "required"
		));

		DisplayPlugin::toggle("cc_area", (isset($cc)));
		$this->addInput("cc", array(
			"name" => "Reply[cc]",
			"value" => $cc,
			"readonly" => true
		));

		$this->addInput("subject", array(
			"name" => "Reply[subject]",
			"value" => "",
			"atr:required" => "required"
		));

		$this->addTextArea("reply_content", array(
			"name" => "Reply[content]",
			"value" => "",
			"attr:required" => "required"
		));
	}

	private function getMailAddressByInquiry(SOYInquiry_Inquiry $inquiry){
		$mailAddress = null;

		if(strpos($inquiry->getContent(), "@")){
			$cols = explode("\n", $inquiry->getContent());
			for($i = 0; $i < count($cols); $i++){
				if(strpos($cols[$i], "@")) {
					preg_match('/([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/i', $cols[$i], $matches);
					if(isset($matches[0]) && strlen($matches[0])){
						$mailAddress = trim($matches[0]);
						break;
					}
				}
			}
		}

		return $mailAddress;
	}

	private function getFromAndCc(){
		$session = SOY2ActionSession::getUserSession();

		$from = $session->getAttribute("email");
		$cc = null;
		$serverConfig = SOY2DAOFactory::create("SOYInquiry_ServerConfigDAO")->get();
		if(is_null($from) || !strlen($from)){
			$from = $serverConfig->getAdministratorMailAddress();
		}else{
            if($this->form->getConfigObject()->getIsCcOnReplyForm()){
                $cc = $serverConfig->getAdministratorMailAddress();
            }
		}

		return array($from, $cc);
	}
}
