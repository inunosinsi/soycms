<?php

class IndexPage extends MainMyPagePageBase{

	function doPost(){
		if(soy2_check_token() && soy2_check_referer()){
			$this->getMyPage()->setAttribute("inquiry.content", $_POST["Inquiry"]);
			if(self::checkValidate()){
				$this->jump("inquiry/confirm");
			}else{
				$this->jump("inquiry");
			}
		}
	}

	private function checkValidate(){
		$errors = array();
		$res = true;

		$inquiry = $_POST["Inquiry"];
		if(!isset($inquiry["content"]) || !strlen($inquiry["content"])){
			$errors["content"] = "お問い合わせ内容が入力されていません。";
		}

		if(count($errors)) $this->getMyPage()->setAttribute("inquiry.error", $errors);

		return (!count($errors));
	}

	function __construct(){
		$this->checkIsLoggedIn(); //ログインチェック

		//お気に入り登録プラグインがアクティブでない場合はトップページに飛ばす
		SOY2::import("util.SOYShopPluginUtil");
		if(!SOYShopPluginUtil::checkIsActive("inquiry_on_mypage")) $this->jumpToTop();

		SOY2::import("module.plugins.inquiry_on_mypage.util.InquiryOnMypageUtil");

		parent::__construct();

		self::hasError();
		self::buildForm();

		$this->addLink("top_link", array(
			"link" => soyshop_get_mypage_top_url()
		));

		//エラーのみ削除
		$this->getMyPage()->clearAttribute("inquiry.error");
	}

	private function hasError(){
		$errors = $this->getMyPage()->getAttribute("inquiry.error");

		DisplayPlugin::toggle("has_error", (isset($errors) && count($errors)));

		foreach(array("content") as $t){
			DisplayPlugin::toggle($t . "_error", (isset($errors[$t])));
			$this->addLabel($t. "_error", array(
				"text" => (isset($errors[$t])) ? $errors[$t] : ""
			));
		}
	}

	function buildForm(){
		$obj = self::getInquiryObject();

		$this->addForm("form");

		/** 隠しモード：要件を拡張機能とhiddenで任意の値を渡す。GETでplugin_idを渡すことで使用できる **/
		$reqVal = (isset($_GET["plugin_id"])) ? self::_getExtensionRequirementValue($_GET["plugin_id"]) : "";
		$opts = (!strlen($reqVal)) ? self::getOptions() : array();

		DisplayPlugin::toggle("requirement", count($opts) > 0);
		$this->addSelect("requirement", array(
			"name" => "Inquiry[requirement]",
			"options" => $opts,
			"selected" => $obj->getRequirement()
		));

		/** 隠しモード：要件を拡張機能とhiddenで任意の値を渡す。GETでplugin_idを渡すことで使用できる **/
		DisplayPlugin::toggle("requirement_extension", strlen($reqVal));
		$this->addInput("requirement_hidden", array(
			"name" => "Inquiry[requirement]",
			"value" => $reqVal
		));


		$this->addLabel("requirement_text", array(
			"text" => $obj->getRequirement()
		));

		$this->addTextArea("content", array(
			"name" => "Inquiry[content]",
			"value" => $obj->getContent()
		));

		$this->addLabel("content_text", array(
			"html" => nl2br(htmlspecialchars($obj->getContent(), ENT_QUOTES, "UTF-8"))
		));
	}

	function getInquiryObject(){
		SOY2::imports("module.plugins.inquiry_on_mypage.domain.*");
		$v = $this->getMyPage()->getAttribute("inquiry.content");
		$obj = (isset($v)) ? SOY2::cast("SOYShop_Inquiry", $v) : new SOYShop_Inquiry();
		$obj->setUserId($this->getUser()->getId());
		return $obj;
	}

	private function _getExtensionRequirementValue($pluginId){
		$pluginId = trim($pluginId);
		if(!strlen($pluginId)) return "";

		$plugin = SOYShopPluginUtil::getPluginById($pluginId);
		if(!$plugin->getIsActive()) return "";

		SOYShopPlugin::load("soyshop.mypage.inquiry", $plugin);
		return SOYShopPlugin::invoke("soyshop.mypage.inquiry", array(
			"mode" => "requirement"
		))->getValue();
	}

	private function getOptions(){
		$config = InquiryOnMypageUtil::getConfig();
		if(!isset($config["requirement"]) || !strlen($config["requirement"])) return array();

		$opts = explode("\n", $config["requirement"]);
		$list = array();
		for ($i = 0; $i < count($opts); $i++){
			$opt = trim($opts[$i]);
			if(!strlen($opt)) continue;
			$list[] = $opt;
		}

		return $list;
	}
}
