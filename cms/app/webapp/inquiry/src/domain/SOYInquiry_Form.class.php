<?php
/**
 * @table soyinquiry_form
 */
class SOYInquiry_Form {

	/**
	 * @id
	 */
    private $id;

    /**
     * @column form_id
     */
    private $formId;

    private $name;

    private $config;

    /**
     * @no_persistent
     */
    private $configObject;

    function getId() {
    	return $this->id;
    }
    function setId($id) {
    	$this->id = $id;
    }
    function getFormId() {
    	return $this->formId;
    }
    function setFormId($formId) {
    	$this->formId = $formId;
    }
    function getName() {
    	return $this->name;
    }
    function setName($name) {
    	$this->name = $name;
    }
    function getConfig() {
    	return $this->config;
    }
    function setConfig($config) {
    	$this->config = $config;
    }
    function getConfigObject(){
    	if(!$this->configObject instanceof SOYInquiry_FormConfig){
    		if(strlen($this->config)){
    			$this->configObject = unserialize($this->config);
    		}else{
    			$this->configObject = new SOYInquiry_FormConfig();
    		}
    	}

    	return $this->configObject;
    }
    function setConfigObject($obj){
    	$this->configObject = $obj;
    	$this->config = serialize($obj);
    }

    /**
     * Designに設定出来る値を表示する
     */
    function getDesignList(){
    	$dir = SOY2::RootDir() . "template/";
    	$files = scandir($dir);

    	$list = array();
    	foreach($files as $file){
    		if($file[0] == "." || strpos($file[0], "_") === 0)continue;
    		$list[] = $file;
    	}

    	return $list;
    }
}


/**
 * 設定オブジェクト
 */
class SOYInquiry_FormConfig{

	private $isSendNotifyMail = true;
	private $isSendConfirmMail = true;
	private $isIncludeAdminURL = true;
	private $isUseCaptcha = false;
	private $isSmartPhone = false;
	private $isReplyToUser = false;

	//お問い合わせ詳細からの返答設定
	private $isCcOnReplyForm = false;

	private $administratorMailAddress = "";
	private $notifyMailSubject = "[SOYInquiry]問い合わせがあります";

	private $fromAddress = "";
	private $returnAddress = "";

	private $fromAddressName = "";
	private $returnAddressName = "";

	private $message = array(
		"information" => "下記の項目を入力してください。",
		"confirm" => "送信内容を確認して下さい。",
		"complete" => "送信いたしました。",
		"require_error" => "この項目は必須です。",
	);

	private $confirmMail = array(
		"title" => "お問い合わせありがとうございます。",
		"header" => "#NAME#様\r\n\r\n今回は○○にお問い合わせありがとうござます。\r\n近日中に返答いたします。\r\n",
		"isOutputContent" => false,
		"footer" => "\r\n\r\n株式会社○○\r\nTEL:XXX-XXX-XXX\r\n住所:東京都千代田区",
		"replaceTrackingNumber" => "#TRACKNUM#"
	);

	private $design = array(
		"theme" => "",
		"isOutputStylesheet" => true
	);

	//SOY Shop連携用
	private $connect = array(
		"siteId" => 0
	);


	function getIsSendNotifyMail() {
		return $this->isSendNotifyMail;
	}
	function setIsSendNotifyMail($isSendNotifyMail) {
		$this->isSendNotifyMail = $isSendNotifyMail;
	}
	function getIsSendConfirmMail() {
		return $this->isSendConfirmMail;
	}
	function setIsSendConfirmMail($isSendConfirmMail) {
		$this->isSendConfirmMail = $isSendConfirmMail;
	}
	function getIsIncludeAdminURL() {
		return $this->isIncludeAdminURL;
	}
	function setIsIncludeAdminURL($isIncludeAdminURL) {
		$this->isIncludeAdminURL = $isIncludeAdminURL;
	}
	function getAdministratorMailAddress() {
		return $this->administratorMailAddress;
	}
	function setAdministratorMailAddress($administratorMailAddress) {
		$this->administratorMailAddress = $administratorMailAddress;
	}
	function getMessage() {
		return $this->message;
	}
	function setMessage($message) {
		$this->message = $message;
	}
	function getConfirmMail() {
		return $this->confirmMail;
	}
	function setConfirmMail($confirmMail) {
		$this->confirmMail = $confirmMail;
	}
	function getDesign() {
		return $this->design;
	}
	function setDesign($design) {
		$this->design = $design;
	}
	function getConnect(){
		return $this->connect;
	}
	function setConnect($connect){
		$this->connect = $connect;
	}

	function getIsUseCaptcha() {
		if(!$this->enabledGD()){
			$this->isUseCaptcha = false;
		}
		return $this->isUseCaptcha;
	}

	function setIsUseCaptcha($isUseCaptcha) {
		$this->isUseCaptcha = $isUseCaptcha;
	}

	function getIsSmartPhone(){
		return $this->isSmartPhone;
	}

	function setIsSmartPhone($isSmartPhone){
		$this->isSmartPhone = $isSmartPhone;
	}

	function getNotifyMailSubject() {
		return $this->notifyMailSubject;
	}
	function setNotifyMailSubject($notifyMailSubject) {
		$this->notifyMailSubject = $notifyMailSubject;
	}

	/**
	 * theme
	 */
	function getTheme(){
		return (@$this->design["theme"]) ? @$this->design["theme"] : "default";
	}

	/**
	 * styleを出力するかどうか
	 */
	function isOutputDesign(){
		return (boolean)$this->design["isOutputStylesheet"];
	}

	/**
	 * GDが使えるかどうかチェック
	 *
	 * @return boolean
	 */
	function enabledGD(){
		if( function_exists("imagejpeg") && function_exists("imagettftext") && function_exists("imagettfbbox") ){
			return true;
		}

		return false;
	}

	function getFromAddress() {
		return $this->fromAddress;
	}
	function setFromAddress($fromAddress) {
		$this->fromAddress = $fromAddress;
	}
	function getReturnAddress() {
		return $this->returnAddress;
	}
	function setReturnAddress($returnAddress) {
		$this->returnAddress = $returnAddress;
	}

	function getFromAddressName() {
		return $this->fromAddressName;
	}
	function setFromAddressName($fromAddressName) {
		$this->fromAddressName = $fromAddressName;
	}
	function getReturnAddressName() {
		return $this->returnAddressName;
	}
	function setReturnAddressName($returnAddressName) {
		$this->returnAddressName = $returnAddressName;
	}

	public function getIsReplyToUser() {
		return $this->isReplyToUser;
	}
	public function setIsReplyToUser($isReplyToUser) {
		$this->isReplyToUser = $isReplyToUser;
	}

	function getIsCcOnReplyForm(){
		return $this->isCcOnReplyForm;
	}
	function setIsCcOnReplyForm($isCcOnReplyForm){
		$this->isCcOnReplyForm = $isCcOnReplyForm;
	}
}
