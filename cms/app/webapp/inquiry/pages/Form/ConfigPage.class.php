<?php

class ConfigPage extends WebPage{
	
	var $id;
	var $dao;
	var $form;
	var $errorMessage;
	
	function doPost(){
		
		if(soy2_check_token()){
		
			try{
	    		$this->form = $this->dao->getById($this->id);
	    	}catch(Exception $e){
	    		CMSApplication::jump("Form");
	    	}
			
			if(isset($_POST["config"])){
				return $this->saveConfig();
			}
			
			if(isset($_POST["mail"])){
				return $this->saveMailConfig();
			}
			
			if(isset($_POST["message"])){
				return $this->saveMessageConfig();
			}
			
			if(isset($_POST["confirmmail"])){
				return $this->saveConfirmMailConfig();
			}
			
			if(isset($_POST["design"])){
				return $this->saveDesignConfig();
			}
			
			if(isset($_POST["connect"])){
				return $this->saveConnectConfig();
			}
			
		}
		
	}
	
	function prepare(){
		$this->dao = SOY2DAOFactory::create("SOYInquiry_FormDAO");
    	$this->form = new SOYInquiry_Form();
    	
    	parent::prepare();
	}

    function ConfigPage($args) {
    	
    	if(count($args)<1)CMSApplication::jump("Form");
    	$this->id = $args[0];
    	
    	WebPage::WebPage();
    	
    	try{
    		$this->form = $this->dao->getById($this->id);
    	}catch(Exception $e){
    		CMSApplication::jump("Form");
    	}
    	
    	$this->createAdd("form_name","HTMLLabel",array(
    		"text" => $this->form->getName()
    	));
    	
    	$this->createAdd("design_link","HTMLLink",array(
    		"link" => SOY2PageController::createLink(APPLICATION_ID . ".Form.Design.".$this->id)
    	));
    	
    	$this->createAdd("preview_link","HTMLLink",array(
    		"link" => SOY2PageController::createLink(APPLICATION_ID . ".Form.Preview.".$this->id),
    		"onclick" => "if(window.previewframe)window.previewframe.close();window.previewframe = new soycms.UI.TargetWindow(this);return false;"
    	));

    	$this->createAdd("template_link","HTMLLink",array(
    		"link" => SOY2PageController::createLink(APPLICATION_ID . ".Form.Template.".$this->id),
    	));
    	
		//詳細設定の出力
		$this->outputConfigForm();
		
		//メール設定の出力
		$this->outputMailForm();
		
		//メッセージ設定の出力
		$this->outputMessageForm();
		
		//確認メール
		$this->outputConfirmMailForm();
		
		//デザイン
		$this->outputDesignForm();
		
		//SOYShop連携
		$this->outputConnectForm();
    }
    
    /**
     * 基本設定画面を表示
     */
    function outputConfigForm(){
    	$this->createAdd("config_form","HTMLForm",array(
    		"action" => SOY2PageController::createLink(APPLICATION_ID . ".Form.Config." . $this->form->getId() . "#config_form")
    	));
    	
    	$this->createAdd("config_form_name","HTMLInput",array(
    		"name" => "Form[name]",
			"value" => $this->form->getName()
    	));	
    	
    	$this->createAdd("config_form_id","HTMLInput",array(
    		"name" => "Form[formId]",
			"value" => $this->form->getFormId(),
    	));	
    	
    	$config = $this->form->getConfigObject();

    	$this->createAdd("config_notUseCaptcha","HTMLInput",array(
    		"type" => "hidden",
			"name" => "Config[isUseCaptcha]",
			"value" => "0"
    	));		
    	$this->createAdd("config_isUseCaptcha","HTMLCheckBox",array(
    		"name" => "Config[isUseCaptcha]",
			"value" => "1",
			"selected" => $config->getIsUseCaptcha(),
			"label" => "CAPCTHAを使用する",
			"disabled" => !$config->enabledGD()
    	));
    	
    	$this->createAdd("config_notSmartPhone","HTMLInput",array(
    		"type" => "hidden",
			"name" => "Config[isSmartPhone]",
			"value" => "0"
    	));
    	$this->createAdd("config_isSmartPhone","HTMLCheckBox",array(
    		"name" => "Config[isSmartPhone]",
    		"value" => "1",
    		"selected" => $config->getIsSmartPhone(),
    		"label" => "スマートフォンからの閲覧の場合、スマートフォン用のカラムを使用する",
    	));
    	
    	$this->createAdd("gd_disabled","HTMLModel",array(
    		"visible" => !$config->enabledGD()
    	));
    	
    }
    
    /**
     * 基本設定を保存
     */
    function saveConfig(){
    	$form = $_POST["Form"];
    	
    	$failed = false;
    	
    	while(true){
    	
	    	//フォームID
	    	$formId = $form["formId"];
	    	$formId = $this->form->getFormId();
			if(strlen($formId) < 0 || strlen($formId) > 512
				|| !preg_match('/^[a-zA-Z_0-9]+$/',$formId)
			){
				$this->errorMessage = '<p class="error">フォームIDは半角英数字のみ指定可能です。</p>';
				$failed = true;
				break;
			}
			
	    	try{
	    		$tmpForm = $this->dao->getByFormId($formId);
	    		if($tmpForm->getId() == $this->id){
	    			throw new Exception();
	    		}
				$this->errorMessage = '<p class="error">フォームID '.htmlspecialchars($formId).' はすでに他のフォームで使われています。</p>';
	    		$failed = true;
	    		break;
	    	}catch(Exception $e){
	    			
	    	}
	    	
	    	SOY2::cast($this->form,(object)$form);
	    	
	    	//
	    	$config = $_POST["Config"];
	    	$configObj = $this->form->getConfigObject();
	    	SOY2::cast($configObj,(object)$config);
	    	
	    	$this->form->setConfigObject($configObj);
	    	
	    	$this->dao->update($this->form);
	    	
	    	CMSApplication::jump("Form.Config." . $this->id . "#config_form");
	    	
	    	break;
	    
    	}
    	
    }

    /**
     * メール設定画面を表示
     */
    function outputMailForm(){
    	$this->createAdd("mail_form","HTMLForm",array(
    		"action" => SOY2PageController::createLink(APPLICATION_ID . ".Form.Config." . $this->form->getId() . "#mail_form")
    	));
    	
    	$config = $this->form->getConfigObject();
    	
    	//管理者宛メールの設定
    	$this->createAdd("config_notSendNotifyMail","HTMLCheckbox",array(
    		"name" => "Mail[isSendNotifyMail]",
			"value" => "0"
    	));		
    	$this->createAdd("config_isSendNotifyMail","HTMLCheckbox",array(
    		"name" => "Mail[isSendNotifyMail]",
			"value" => "1",
			"selected" => $config->getIsSendNotifyMail(),
			"label" => "管理者に通知メールを送信する"
    	));
    	$this->createAdd("config_administratorMailAddress","HTMLInput",array(
    		"name" => "Mail[administratorMailAddress]",
			"value" => $config->getAdministratorMailAddress()
    	));	
    	
    	$this->createAdd("config_notifyMailSubject","HTMLInput",array(
    		"name" => "Mail[notifyMailSubject]",
			"value" => $config->getNotifyMailSubject()
    	));

    	$this->createAdd("config_notIncludeAdminURL","HTMLCheckbox",array(
    		"name" => "Mail[isIncludeAdminURL]",
			"value" => "0"
    	));		
    	$this->createAdd("config_isIncludeAdminURL","HTMLCheckbox",array(
    		"name" => "Mail[isIncludeAdminURL]",
			"value" => "1",
			"selected" => $config->getIsIncludeAdminURL(),
			"label" => "管理画面のURLをメール本文に含める"
    	));
    	
    	$this->createAdd("config_notReplyToUser","HTMLCheckbox",array(
    		"name" => "Mail[isReplyToUser]",
			"value" => "0"
    	));		
    	$this->createAdd("config_isReplyToUser","HTMLCheckbox",array(
    		"name" => "Mail[isReplyToUser]",
			"value" => "1",
			"selected" => $config->getIsReplyToUser(),
			"label" => "メールの返信先にユーザーのメールアドレスを追加する"
    	));
    	
    	
    	//ユーザー宛メールの設定
    	$this->createAdd("config_notSendConfirmMail","HTMLCheckbox",array(
    		"name" => "Mail[isSendConfirmMail]",
			"value" => "0"
    	));	
    	
    	$this->createAdd("config_isSendConfirmMail","HTMLCheckbox",array(
    		"name" => "Mail[isSendConfirmMail]",
			"value" => "1",
			"selected" => $config->getIsSendConfirmMail(),
			"label" => "ユーザに確認メールを送信する"
    	));
    	
    	$this->createAdd("config_fromAddress","HTMLInput",array(
    		"name" => "Mail[fromAddress]",
			"value" => $config->getFromAddress()
    	));
    	
    	$this->createAdd("config_returnAddress","HTMLInput",array(
    		"name" => "Mail[returnAddress]",
			"value" => $config->getReturnAddress()
    	));
    	
    	$this->createAdd("config_fromAddressName","HTMLInput",array(
    		"name" => "Mail[fromAddressName]",
			"value" => $config->getFromAddressName()
    	));
    	
    	$this->createAdd("config_returnAddressName","HTMLInput",array(
    		"name" => "Mail[returnAddressName]",
			"value" => $config->getReturnAddressName()
    	));
    	
    }
    
    /**
     * メール設定を保存
     */
    function saveMailConfig(){
    	
    	$config = $_POST["Mail"];
    	$configObj = $this->form->getConfigObject();
    	SOY2::cast($configObj,(object)$config);
    	
    	$this->form->setConfigObject($configObj);
    	
    	$this->dao->update($this->form);
    	
    	CMSApplication::jump("Form.Config." . $this->id . "#mail_form");
	    	
    }
        
    /**
     * メッセージ設定
     */
    function outputMessageForm(){
    	$this->createAdd("message_form","HTMLForm",array(
    		"action" => SOY2PageController::createLink(APPLICATION_ID . ".Form.Config." . $this->form->getId() . "#message_form")
    	));
    	
    	$configObject= $this->form->getConfigObject();
    	$message = $configObject->getMessage();
    	
    	$this->createAdd("message_information","HTMLTextArea",array(
    		"name" => "Message[information]",
    		"value" => $message["information"]
    	));
    	
    	$this->createAdd("message_confirm","HTMLTextArea",array(
    		"name" => "Message[confirm]",
    		"value" => $message["confirm"]
    	));
    	
    	$this->createAdd("message_complete","HTMLTextArea",array(
    		"name" => "Message[complete]",
    		"value" => $message["complete"]
    	));
    	
    }
    
    /**
     * メッセージ設定保存
     */
    function saveMessageConfig(){
    	$postMessage = $_POST["Message"];
    	
    	
    	$configObject= $this->form->getConfigObject();
    	$message = $configObject->getMessage();
    	if(isset($postMessage["information"]))$message["information"] = $postMessage["information"];
		if(isset($postMessage["confirm"]))$message["confirm"] = $postMessage["confirm"];
		if(isset($postMessage["complete"]))$message["complete"] = $postMessage["complete"];
	
    	$configObject->setMessage($message);
    	$this->form->setConfigObject($configObject);
    	
    	$this->dao->update($this->form);
	    	
		CMSApplication::jump("Form.Config." . $this->id . "#message_form");
	    	    
    		
    }
    
    /**
     * 詳細メール設定
     */
    function outputConfirmMailForm(){
    	$this->createAdd("confirmmail_form","HTMLForm",array(
    		"action" => SOY2PageController::createLink(APPLICATION_ID . ".Form.Config." . $this->form->getId() . "#confirmmail_form")
    	));
    	
    	$confirmMail = $this->form->getConfigObject()->getConfirmMail();
    	
    	$this->createAdd("confirmmail_title","HTMLInput",array(
    		"name" => "ConfirmMail[title]",
    		"value" => $confirmMail["title"]
    	));
    	
    	$this->createAdd("confirmmail_notoutput_content","HTMLInput",array(
    		"name" => "ConfirmMail[isOutputContent]",
    		"value" => "0"
    	));
    	
    	$this->createAdd("confirmmail_isoutput_content","HTMLCheckBox",array(
    		"name" => "ConfirmMail[isOutputContent]",
    		"value" => "1",
    		"selected" => $confirmMail["isOutputContent"],
    		"label" => "お問い合わせ内容を出力する"
    	));
    	$this->createAdd("replace_trackingnumber","HTMLInput",array(
    		"name" => "ConfirmMail[replaceTrackingNumber]",
    		"value" => @$confirmMail["replaceTrackingNumber"]
    	));
    	$this->createAdd("confirmmail_header","HTMLTextArea",array(
    		"name" => "ConfirmMail[header]",
    		"value" => $confirmMail["header"]
    	));
    	$this->createAdd("confirmmail_footer","HTMLTextArea",array(
    		"name" => "ConfirmMail[footer]",
    		"value" => $confirmMail["footer"]
    	));
    	
    	$this->createAdd("replace_trackingnumber_text", "HTMLLabel", array(
    		"text" => @$confirmMail["replaceTrackingNumber"]
    	));
    }
    
    /**
     * メッセージ設定保存
     */
    function saveConfirmMailConfig(){
    	$post = $_POST["ConfirmMail"];
    	
    	$configObject= $this->form->getConfigObject();
    	$confirmMail = $configObject->getConfirmMail();
    	if(isset($post["title"]))$confirmMail["title"] = $post["title"];
		if(isset($post["header"]))$confirmMail["header"] = $post["header"];
		if(isset($post["isOutputContent"]))$confirmMail["isOutputContent"] = $post["isOutputContent"];
		if(isset($post["footer"]))$confirmMail["footer"] = $post["footer"];
		if(isset($post["replaceTrackingNumber"]))$confirmMail["replaceTrackingNumber"] = $post["replaceTrackingNumber"];
			
    	$configObject->setConfirmMail($confirmMail);
    	$this->form->setConfigObject($configObject);
    	
    	$this->dao->update($this->form);
	
		CMSApplication::jump("Form.Config." . $this->id . "#confirmmail_form");
    }
    
    /**
     * デザイン設定
     */
    function outputDesignForm(){
    	$this->createAdd("design_form","HTMLForm",array(
    		"action" => SOY2PageController::createLink(APPLICATION_ID . ".Form.Config." . $this->form->getId() . "#design_form")
    	));
    	
    	$design = $this->form->getConfigObject()->getDesign();
    	
    	
    	$this->createAdd("design_notoutput_stylesheet","HTMLInput",array(
    		"name" => "Design[isOutputStylesheet]",
    		"value" => "0"
    	));
    	
    	$this->createAdd("design_theme","HTMLSelect",array(
    		"name" => "Design[theme]",
    		"options" => $this->form->getDesignList(),
    		"selected" => $design["theme"]
    	));
    	
    	$this->createAdd("design_isoutput_stylesheet","HTMLCheckBox",array(
    		"name" => "Design[isOutputStylesheet]",
    		"value" => "1",
    		"selected" => $design["isOutputStylesheet"],
    		"label" => "スタイルシートを読み込む"
    	));
    	
    }
    
    /**
     * デザイン設定保存
     */
    function saveDesignConfig(){
    	$post = $_POST["Design"];
    	
    	$configObject= $this->form->getConfigObject();
    	$design = $configObject->getDesign();
    	if(isset($design["isOutputStylesheet"]))$design["isOutputStylesheet"] = $post["isOutputStylesheet"];
    	if(isset($design["theme"]))$design["theme"] = $post["theme"];

    	$configObject->setDesign($design);
    	$this->form->setConfigObject($configObject);
    	
    	$this->dao->update($this->form);
	
		CMSApplication::jump("Form.Config." . $this->id . "#design_form");
    }
    
    /**
     * SOYShop連携設定
     */
    function outputConnectForm(){
    	$this->addForm("connect_form",array(
    		"action" => SOY2PageController::createLink(APPLICATION_ID . ".Form.Config." . $this->form->getId() . "#connect_form")
    	));
    	
   		$connect = $this->form->getConfigObject()->getConnect();
    	
    	$connectLogic = SOY2Logic::createInstance("logic.SOYShopConnectLogic");
    	
    	$this->addModel("check_version",array(
			"visible" => ($connectLogic->checkVersion() === false)
		));
		
		$this->addSelect("connect_sites",array(
			"name" => "Connect[siteId]",
			"options" => $connectLogic->getSOYShopSiteList(),
			"selected" => (isset($connect["siteId"])) ? $connect["siteId"] : false
		));
    }
    
    /**
     * デザイン設定保存
     */
    function saveConnectConfig(){
    	$post = $_POST["Connect"];
    	
    	$configObject = $this->form->getConfigObject();
    	$connect = $configObject->getConnect();
		if(isset($connect["siteId"]))$connect["siteId"] = $post[""] = (int)$post["siteId"];

		$configObject->setConnect($connect);
    	$this->form->setConfigObject($configObject);
    	
    	$this->dao->update($this->form);
	
		CMSApplication::jump("Form.Config." . $this->id . "#connect_form");
    }
    
    
    /* 共通処理 */
    function getForm(){
    	try{
    		return $this->dao->getById($this->id);
    	}catch(Exception $e){
    		//
    	}
    	
    	return null;
    }
}

?>