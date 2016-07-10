<?php

class DetailPage extends WebPage{
	
	private $id;
	
	function doPost(){
		
		if(soy2_check_token() && isset($_POST["Mail"])){
			
			//タイトルが存在していない場合
			if(!isset($_POST["Mail"]["title"]) || strlen($_POST["Mail"]["title"]) === 0) CMSApplication::jump("Mail.Detail." . $this->id . "?failed");
			
			//更新の場合
			if(isset($this->id)){
				$old = self::getMailObj($this->id);
				$mailObj = SOY2::cast($old, $_POST["Mail"]);
				try{
					self::mailDao()->update($mailObj);
					CMSApplication::jump("Mail.Detail." . $this->id . "?updated");
				}catch(Exception $e){
					
				}
				
			//新規作成の場合
			}else{
				$dao = self::mailDao();
				$mailObj = SOY2::cast("StepMail_Mail", $_POST["Mail"]);
				try{
					$this->id = $dao->insert($mailObj);
					CMSApplication::jump("Mail.Detail." . $this->id . "?created");
				}catch(Exception $e){
					
				}
			}
			
			
			CMSApplication::jump("Mail.Detail." . $this->id . "?failed");
		}
	}
	
	function DetailPage($args){
		$this->id = (isset($args[0])) ? (int)$args[0] : null;	//nullの時は新規作成
		
		WebPage::WebPage();
		
		//詳細画面
		self::buildDetailArea();
		
		//登録画面
		self::buildRegisterForm();
	}
	
	private function buildDetailArea(){
		DisplayPlugin::toggle("detail_area", isset($this->id));
		
		DisplayPlugin::toggle("successed", isset($_GET["successed"]));
		DisplayPlugin::toggle("failed", isset($_GET["failed"]));
		
		$this->addLink("add_step_link", array(
			"link" => CMSApplication::createLink("Mail.Step?mail_id=" . $this->id)
		));
		
		
		$this->createAdd("step_list", "_common.StepListComponent", array(
			"list" => self::getStepList(),
			"mailId" => $this->id
		));
	}
	
	private function getStepList(){
		try{
			return self::stepDao()->getByMailId($this->id);
		}catch(Exception $e){
			return array();
		}
	}
	
	private function stepDao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("StepMail_StepDAO");
		return $dao;
	}
	
	private function buildRegisterForm(){
		DisplayPlugin::toggle("updated", isset($_GET["updated"]));
		DisplayPlugin::toggle("created", isset($_GET["created"]));
		DisplayPlugin::toggle("failed", isset($_GET["failed"]));

		$mail = self::getMailObj();

		$this->addForm("register_form");
		
		$this->addInput("title", array(
			"name" => "Mail[title]",
			"value" => $mail->getTitle(),
			"attr:required" => "required"
		));
		
		$this->addInput("mail_id", array(
			"name" => "Mail[mailId]",
			"value" => $mail->getMailId(),
			"attr:required" => "required"
		));
		
		$this->addInput("overview", array(
			"name" => "Mail[overview]",
			"value" => $mail->getOverview()
		));
		
		$this->addModel("submit_button", array(
			"type" => "submit",
			"attr:value" => (isset($this->id)) ? "更新" : "作成"
		));
	}
	
	private function getMailObj(){
		try{
			return self::mailDao()->getById($this->id);
		}catch(Exception $e){
			return new StepMail_Mail();
		}
	}
	
	private function mailDao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("StepMail_MailDAO");
		return $dao;
	}
}
?>