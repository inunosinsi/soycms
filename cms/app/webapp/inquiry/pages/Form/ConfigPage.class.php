<?php

SOY2HTMLFactory::importWebPage("_common.FormPageBase");
class ConfigPage extends FormPageBase{

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

    function __construct($args) {

    	if(count($args)<1)CMSApplication::jump("Form");
    	$this->id = $args[0];

    	parent::__construct();

    	try{
    		$this->form = $this->dao->getById($this->id);
    	}catch(Exception $e){
    		CMSApplication::jump("Form");
    	}

    	$this->addLabel("form_name", array(
    		"text" => $this->form->getName()
    	));

    	$this->addLink("design_link", array(
    		"link" => SOY2PageController::createLink(APPLICATION_ID . ".Form.Design.".$this->id)
    	));

    	// $this->createAdd("preview_link","HTMLLink",array(
    	// 	"link" => SOY2PageController::createLink(APPLICATION_ID . ".Form.Preview.".$this->id),
    	// 	"onclick" => "if(window.previewframe)window.previewframe.close();window.previewframe = new soycms.UI.TargetWindow(this);return false;"
    	// ));

		$this->addLabel("preview_modal", array(
			"html" => $this->buildModal($this->id, self::MODE_PREVIEW)
		));

    	$this->addLink("template_link", array(
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
    	$this->addForm("config_form", array(
    		"action" => SOY2PageController::createLink(APPLICATION_ID . ".Form.Config." . $this->form->getId() . "#config_form")
    	));

    	$this->addInput("config_form_name", array(
    		"name" => "Form[name]",
			"value" => $this->form->getName()
    	));

    	$this->addInput("config_form_id", array(
    		"name" => "Form[formId]",
			"value" => $this->form->getFormId(),
    	));

    	$config = $this->form->getConfigObject();

    	$this->addInput("config_notUseCaptcha", array(
    		"type" => "hidden",
			"name" => "Config[isUseCaptcha]",
			"value" => "0"
    	));
    	$this->addCheckBox("config_isUseCaptcha", array(
    		"name" => "Config[isUseCaptcha]",
			"value" => "1",
			"selected" => $config->getIsUseCaptcha(),
			"label" => "CAPCTHAを使用する",
			"disabled" => !$config->enabledGD()
    	));

		// GoogleのreCAPTCHA v3が使用できる場合はSOY CMSのreCAPTCHA v3の使用を促す
		DisplayPlugin::toggle("enabledGoogleReCAPTCHA_v3", function_exists("curl_init"));

		$this->addInput("config_noCsrf", array(
			"type" => "hidden",
			"name" => "Config[isCsrf]",
			"value" => 0
		));

		$this->addCheckBox("config_isCsrf", array(
			"name" => "Config[isCsrf]",
			"value" => 1,
			"selected" => $config->getIsCsrf(),
			"label" => "トークンチェックを有効にする"
		));

    	$this->addInput("config_notSmartPhone", array(
    		"type" => "hidden",
			"name" => "Config[isSmartPhone]",
			"value" => "0"
    	));
    	$this->addCheckBox("config_isSmartPhone", array(
    		"name" => "Config[isSmartPhone]",
    		"value" => "1",
    		"selected" => $config->getIsSmartPhone(),
    		"label" => "スマートフォンからの閲覧の場合、スマートフォン用のカラムを使用する",
    	));

    	$this->addModel("gd_disabled", array(
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
    	$this->addCheckBox("config_notSendNotifyMail", array(
    		"name" => "Mail[isSendNotifyMail]",
			"value" => "0"
    	));
    	$this->addCheckBox("config_isSendNotifyMail", array(
    		"name" => "Mail[isSendNotifyMail]",
			"value" => "1",
			"selected" => $config->getIsSendNotifyMail(),
			"label" => "管理者に通知メールを送信する"
    	));
    	$this->addInput("config_administratorMailAddress", array(
    		"name" => "Mail[administratorMailAddress]",
			"value" => $config->getAdministratorMailAddress()
    	));

    	$this->addInput("config_notifyMailSubject", array(
    		"name" => "Mail[notifyMailSubject]",
			"value" => $config->getNotifyMailSubject()
    	));

		$this->addTextArea("config_notifyMailIncludeHeadline", array(
			"name" => "Mail[notifyMailIncludeHeadline]",
			"value" => $config->getNotifyMailIncludeHeadline(),
			"style" => "height:80px !important;"
		));

		$this->addTextArea("config_notifyMailIncludeRemarks", array(
			"name" => "Mail[notifyMailIncludeRemarks]",
			"value" => $config->getNotifyMailIncludeRemarks(),
			"style" => "height:80px !important;"
		));

    	$this->addCheckBox("config_notIncludeAdminURL", array(
    		"name" => "Mail[isIncludeAdminURL]",
			"value" => "0"
    	));
    	$this->addCheckBox("config_isIncludeAdminURL", array(
    		"name" => "Mail[isIncludeAdminURL]",
			"value" => "1",
			"selected" => $config->getIsIncludeAdminURL(),
			"label" => "管理画面のURLをメール本文に含める"
    	));

    	$this->addCheckBox("config_notReplyToUser", array(
    		"name" => "Mail[isReplyToUser]",
			"value" => "0"
    	));
    	$this->addCheckBox("config_isReplyToUser", array(
    		"name" => "Mail[isReplyToUser]",
			"value" => "1",
			"selected" => $config->getIsReplyToUser(),
			"label" => "メールの返信先をユーザーのメールアドレスにする"
    	));


    	//ユーザー宛メールの設定
    	$this->addCheckBox("config_notSendConfirmMail", array(
    		"name" => "Mail[isSendConfirmMail]",
			"value" => "0"
    	));

    	$this->addCheckBox("config_isSendConfirmMail", array(
    		"name" => "Mail[isSendConfirmMail]",
			"value" => "1",
			"selected" => $config->getIsSendConfirmMail(),
			"label" => "ユーザに確認メールを送信する"
    	));

    	$this->addInput("config_fromAddress", array(
    		"name" => "Mail[fromAddress]",
			"value" => $config->getFromAddress()
    	));

    	$this->addInput("config_returnAddress", array(
    		"name" => "Mail[returnAddress]",
			"value" => $config->getReturnAddress()
    	));

    	$this->addInput("config_fromAddressName", array(
    		"name" => "Mail[fromAddressName]",
			"value" => $config->getFromAddressName()
    	));

    	$this->addInput("config_returnAddressName", array(
    		"name" => "Mail[returnAddressName]",
			"value" => $config->getReturnAddressName()
    	));

        $this->addInput("config_noCcOnReplyForm", array(
            "name" => "Mail[isCcOnReplyForm]",
            "value" => 0
        ));

    	$this->addCheckBox("config_isCcOnReplyForm", array(
    		"name" => "Mail[isCcOnReplyForm]",
    		"value" => "1",
    		"selected" => $config->getIsCcOnReplyForm(),
    		"label" => "CCに管理者のメールアドレスを追加する"
    	));

		/** 置換文字列 使わなくなった **/
		DisplayPlugin::toggle("replacement", false);

		$this->addInput("new_replace_key", array(
			"name" => "Replace[key][]",
			"attr:placeholder" => "例：##REPLACE##"
		));

		$this->addInput("new_replace_text", array(
			"name" => "Replace[text][]"
		));

		$this->createAdd("replace_list", "_common.ReplaceStringListComponent", array(
			"list" => SOYInquiryUtil::getReplacementStringsConfig((int)$this->form->getId())
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

		/** 置換文字列の設定はSOYInquiry_DataSetsに格納する **/
		$repCnfs = array();
		if(isset($_POST["Replace"]) && isset($_POST["Replace"]["key"]) && is_array($_POST["Replace"]["key"]) && count($_POST["Replace"]["key"])){
			$cnt = count($_POST["Replace"]["key"]);
			for($i = 0; $i < $cnt; $i++){
				if(!isset($_POST["Replace"]["key"][$i])) continue;
				$key = trim($_POST["Replace"]["key"][$i]);
				if(!strlen($key)) continue;

				$text = (isset($_POST["Replace"]["text"][$i])) ? trim($_POST["Replace"]["text"][$i]) : "";
				$repCnfs[] = array("key" => $key, "text" => $text);
			}
		}

		SOYInquiryUtil::saveReplacementStringsConfig((int)$this->form->getId(), $repCnfs);

    	CMSApplication::jump("Form.Config." . $this->id . "#mail_form");

    }

    /**
     * メッセージ設定
     */
    function outputMessageForm(){
    	$this->addForm("message_form", array(
    		"action" => SOY2PageController::createLink(APPLICATION_ID . ".Form.Config." . $this->form->getId() . "#message_form")
    	));

    	$configObject= $this->form->getConfigObject();
    	$message = $configObject->getMessage();

    	$this->addTextArea("message_information", array(
    		"name" => "Message[information]",
    		"value" => $message["information"]
    	));

    	$this->addTextArea("message_confirm", array(
    		"name" => "Message[confirm]",
    		"value" => $message["confirm"]
    	));

    	$this->addTextArea("message_complete", array(
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
    	$this->addForm("confirmmail_form", array(
    		"action" => SOY2PageController::createLink(APPLICATION_ID . ".Form.Config." . $this->form->getId() . "#confirmmail_form")
    	));

    	$confirmMail = $this->form->getConfigObject()->getConfirmMail();

    	$this->addInput("confirmmail_title", array(
    		"name" => "ConfirmMail[title]",
    		"value" => $confirmMail["title"]
    	));

    	$this->addInput("confirmmail_notoutput_content", array(
    		"name" => "ConfirmMail[isOutputContent]",
    		"value" => "0"
    	));

    	$this->addCheckBox("confirmmail_isoutput_content", array(
    		"name" => "ConfirmMail[isOutputContent]",
    		"value" => "1",
    		"selected" => $confirmMail["isOutputContent"],
    		"label" => "お問い合わせ内容を出力する"
    	));
    	$this->addInput("replace_trackingnumber", array(
    		"name" => "ConfirmMail[replaceTrackingNumber]",
    		"value" => (isset($confirmMail["replaceTrackingNumber"])) ? $confirmMail["replaceTrackingNumber"] : ""
    	));
    	$this->addTextArea("confirmmail_header", array(
    		"name" => "ConfirmMail[header]",
    		"value" => $confirmMail["header"]
    	));
    	$this->addTextArea("confirmmail_footer", array(
    		"name" => "ConfirmMail[footer]",
    		"value" => $confirmMail["footer"]
    	));

    	$this->addLabel("replace_trackingnumber_text", array(
    		"text" => (isset($confirmMail["replaceTrackingNumber"])) ? $confirmMail["replaceTrackingNumber"] : ""
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
    	$this->addForm("design_form", array(
    		"action" => SOY2PageController::createLink(APPLICATION_ID . ".Form.Config." . $this->form->getId() . "#design_form")
    	));

    	$design = $this->form->getConfigObject()->getDesign();
		
    	$this->addInput("design_notoutput_stylesheet", array(
    		"name" => "Design[isOutputStylesheet]",
    		"value" => "0"
    	));

    	$this->addSelect("design_theme", array(
    		"name" => "Design[theme]",
    		"options" => $this->form->getDesignList(),
    		"selected" => $design["theme"]
    	));

    	$this->addCheckBox("design_isoutput_stylesheet", array(
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

		DisplayPlugin::toggle("check_version", !$connectLogic->checkVersion());

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
    		return null;
    	}
    }
}
