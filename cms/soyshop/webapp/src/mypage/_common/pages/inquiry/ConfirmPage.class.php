<?php
SOY2HTMLFactory::importWebPage("inquiry.IndexPage");
class ConfirmPage extends IndexPage{

	function doPost(){

		if(soy2_check_token() && soy2_check_referer()){
			if(isset($_POST["send"]) || isset($_POST["send_x"])){
				$inquiry = SOY2Logic::createInstance("module.plugins.inquiry_on_mypage.logic.InquiryLogic", array("user" => $this->getUser()))->addInquiry($this->getInquiryObject());
				if(isset($inquiry)){
					$this->jump("inquiry/complete?tracking_number=" . $inquiry->getTrackingNumber());
				}
			}

			if(isset($_POST["back"]) || isset($_POST["back_x"])){
				$this->jump("inquiry");
			}

			$this->jump("inquiry/confirm?error");
		}
	}

	function __construct(){
		//入力内容がなければトップページへ
		$obj = $this->getInquiryObject();
		if(is_null($obj) || !strlen($obj->getContent())) $this->jump("inquiry");

		parent::__construct();
		$this->buildForm();
	}
}
