<?php
/*
 * Created on 2010/07/24
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

SOYShopLoginCheckPlugin::register();

class SOYShopLoginCheckPlugin{

	const PLUGIN_ID = "SOYShopLoginCheck";
	const ALLOW_BROWSE = 1;
	const NOT_ALLOW_BROWSE = 0;

	private $siteId = "shop";
	private $isLoggedIn;
	private $loginPageUrl;
	private $logoutPageUrl;
	private $remindPageUrl;

	private $doRedirectAfterLogin = 1;
	private $doRedirectAfterRemind = 1;


	//特定の商品を購入していないと記事詳細を閲覧できないモード
	private $allowBrowseEntryByPurchased = self::NOT_ALLOW_BROWSE;

	private $userId;

	//フォームへリダイレクトするページ
	//Array<ページID => 0 | 1> リダイレクトしないページが1
	public $config_per_page = array();
	//Array<ページID => Array<ページタイプ => 0 | 1>> リダイレクトしないページが1
	public $config_per_blog = array();

	//コメント投稿者へのポイント付与設定
	private $point = 0;

	//ログインしたユーザの情報をコメントフォームに挿入するか？
	private $isInsertCommentForm;

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID,array(
			"name"=>"SOYShopログインチェックプラグイン",
			"description"=>"SOY Shopサイトでのログインの有無をチェックする<br />このプラグインを使用する時はSOY Shop1.15.0以降のバージョンをご利用ください。",
			"author"=>"日本情報化農業研究所",
			"url"=>"http://www.n-i-agroinformatics.com",
			"mail"=>"info@n-i-agroinformatics.com",
			"version"=>"1.0"
		));

		if(CMSPlugin::activeCheck($this->getId())){

			if(!class_exists("util.SOYShopUtil")) SOY2::import("util.SOYShopUtil");

			//SOY Shopがインストールされていれば動く
			if(SOYShopUtil::checkSOYShopInstall()){

				CMSPlugin::addPluginConfigPage(self::PLUGIN_ID, array(
					$this,"config_page"
				));

				//公開画面側
				if(defined("_SITE_ROOT_")){

					//ここでログインチェックをしてしまう。
					$checkLogic = SOY2Logic::createInstance("site_include.plugin.soyshop_login_check.logic.LoginCheckLogic", array("siteId" => $this->siteId));
					$this->isLoggedIn = $checkLogic->isLoggedIn();

					$loginLogic = SOY2Logic::createInstance("site_include.plugin.soyshop_login_check.logic.LoginLogic", array("siteId" => $this->siteId));

					//ログインページのURLもここで取得する
					if(!$this->isLoggedIn){
						$loginPageUrl = $loginLogic->getLoginPageUrl();
						if($this->doRedirectAfterLogin){
							$this->loginPageUrl = $loginPageUrl."?r=" . rawurldecode($_SERVER["REQUEST_URI"]);
						}else{
							$this->loginPageUrl = $loginPageUrl;
						}

						//パスワードリマインダの設定
						$this->remindPageUrl = str_replace("/login", "/remind/input", $loginPageUrl);
						if($this->doRedirectAfterRemind){
							$this->remindPageUrl .= "?r=" . rawurldecode($_SERVER["REQUEST_URI"]);
						}

					//ログアウトページのURLをここで取得する
					}else{
						$this->logoutPageUrl = $loginLogic->getLogoutPageUrl();
						$this->userId = $checkLogic->getUserId();
					}

					CMSPlugin::setEvent('onEntryOutput',self::PLUGIN_ID, array($this, "onEntryOutput"));
					CMSPlugin::setEvent('onPageOutput', self::PLUGIN_ID, array($this, "onPageOutput"));

					//コメント時のポイント付与
					if($this->isLoggedIn && (is_numeric($this->point) && $this->point > 0)){
						CMSPlugin::setEvent('onSubmitComment',self::PLUGIN_ID, array($this, "onSubmitComment"));
					}
				//管理画面側
				}else{
					//特定の商品を購入していないと記事詳細を閲覧できないモードをアクティブにした時
					if($this->allowBrowseEntryByPurchased == self::ALLOW_BROWSE){
						CMSPlugin::setEvent('onEntryUpdate', self::PLUGIN_ID, array($this, "onEntryUpdate"));
						CMSPlugin::setEvent('onEntryCreate', self::PLUGIN_ID, array($this, "onEntryUpdate"));
						CMSPlugin::setEvent('onEntryCopy', self::PLUGIN_ID, array($this, "onEntryCopy"));
						CMSPlugin::setEvent('onEntryRemove', self::PLUGIN_ID, array($this, "onEntryRemove"));

						CMSPlugin::addCustomFieldFunction(self::PLUGIN_ID, "Entry.Detail", array($this, "onCallCustomField"));
						CMSPlugin::addCustomFieldFunction(self::PLUGIN_ID, "Blog.Entry", array($this, "onCallCustomField_inBlog"));
					}
				}
			}
		}
	}

	function onEntryOutput($arg){

		$htmlObj = $arg["SOY2HTMLObject"];

		$htmlObj->addModel("is_login", array(
			"soy2prefix" => "cms",
			"visible" => ($this->isLoggedIn)
		));

		$htmlObj->addModel("no_login", array(
			"soy2prefix" => "cms",
			"visible" => (!$this->isLoggedIn)
		));

		//ログインリンク
		$htmlObj->addLink("login_link", array(
			"soy2prefix" => "cms",
			"link" => $this->loginPageUrl
		));

		//ログアウトリンク
		$htmlObj->addLink("logout_link", array(
			"soy2prefix" => "cms",
			"link" => $this->logoutPageUrl
		));

		/** ここから下は詳細ページでしか動作しません **/
		if($this->displayDetailPage($htmlObj)){
			$htmlObj->addForm("login_form", array(
				"soy2prefix" => "cms",
				"action" => $this->loginPageUrl,
				"method" => "post"
			));

			$htmlObj->addInput("login_id", array(
				"soy2prefix" => "cms",
				"type" => "text",
				"name" => "loginId",
				"value" => ""
			));

			//後方互換
			$htmlObj->addInput("login_email", array(
				"soy2prefix" => "cms",
				"type" => "email",
				"name" => "loginId",
				"value" => ""
			));

			$htmlObj->addInput("login_password", array(
				"soy2prefix" => "cms",
				"type" => "password",
				"name" => "password",
				"value" => ""
			));

			$htmlObj->addInput("login_submit", array(
				"soy2prefix" => "cms",
				"type" => "submit",
				"name" => "login"
			));

			$htmlObj->addInput("auto_login", array(
				"soy2prefix" => "cms",
				"type" => "checkbox",
				"name" => "login_memory"
			));
		}
	}

	function displayDetailPage($htmlObj){
		if(!isset($htmlObj->entryPageUri)) return false;
		$pageUri = substr($_SERVER["REQUEST_URI"], 0, strrpos($_SERVER["REQUEST_URI"], "/") + 1);
		return (strpos($htmlObj->entryPageUri, $pageUri) !== false);
	}

	function onPageOutput($obj){

		//リダイレクトの対象ページか調べる。
		if($this->checkRedirect($obj->page->getId())){
			$redirectLogic = SOY2Logic::createInstance("site_include.plugin.soyshop_login_check.logic.RedirectLogic", array("loginPageUrl" => $this->loginPageUrl, "configPerBlog" => $this->config_per_blog));
			$mode = isset($obj->mode) ? $obj->mode : null;
			$redirectLogic->redirectLoginForm($obj->page, $mode);
		}

		//商品ごとに調べる
		if(
			property_exists($obj, "mode") &&
			$obj->mode == "_entry_" &&
			$this->allowBrowseEntryByPurchased == self::ALLOW_BROWSE &&
			$this->isLoggedIn
		){
			$entryId = (int)$obj->entry->getId();
			if(!class_exists("RedirectLogic")){
				$redirectLogic = SOY2Logic::createInstance("site_include.plugin.soyshop_login_check.logic.RedirectLogic", array("loginPageUrl" => $this->loginPageUrl, "configPerBlog" => $this->config_per_blog));
			}
			$redirectLogic->redirectItemDetailPage($entryId, $this->siteId);
		}

		/** ここからフォーム **/
		$obj->addModel("is_login", array(
			"soy2prefix" => "s_block",
			"visible" => ($this->isLoggedIn)
		));

		$obj->addModel("no_login", array(
			"soy2prefix" => "s_block",
			"visible" => (!$this->isLoggedIn)
		));

		$obj->addModel("login_error", array(
			"soy2prefix" => "s_block",
			"visible" => (isset($_GET["login"]) && $_GET["login"] == "error")
		));

		$obj->addForm("login_form", array(
			"soy2prefix" => "s_block",
			"action" => $this->loginPageUrl,
			"method" => "post"
		));

		$obj->addInput("login_id", array(
			"soy2prefix" => "s_block",
			"type" => "text",
			"name" => "loginId",
			"value" => ""
		));

		$obj->addInput("login_email", array(
			"soy2prefix" => "s_block",
			"type" => "email",
			"name" => "loginId",
			"value" => ""
		));

		$obj->addInput("login_password", array(
			"soy2prefix" => "s_block",
			"type" => "password",
			"name" => "password",
			"value" => ""
		));

		$obj->addInput("login_submit", array(
			"soy2prefix" => "s_block",
			"type" => "submit",
			"name" => "login"
		));

		$obj->addInput("auto_login", array(
			"soy2prefix" => "s_block",
			"type" => "checkbox",
			"name" => "login_memory"
		));

		$obj->addLink("logout_link", array(
			"soy2prefix" => "s_block",
			"link" => $this->logoutPageUrl
		));

		/** パスワードリマインド **/
		$obj->addForm("remind_form", array(
			"soy2prefix" => "s_block",
			"action" => $this->remindPageUrl,
			"method" => "post"
		));

		$obj->addInput("remind_mail_input", array(
			"soy2prefix" => "s_block",
			"name" => "mail"
		));

		$obj->addModel("remind_before", array(
			"soy2prefix" => "s_block",
			"visible" => (!isset($_GET["send"]) || (isset($_GET["send"]) && $_GET["send"] != "complete"))
		));

		$obj->addModel("remind_error", array(
			"soy2prefix" => "s_block",
			"visible" => (isset($_GET["send"]) && $_GET["send"] == "error")
		));

		$obj->addModel("remind_after", array(
			"soy2prefix" => "s_block",
			"visible" => (isset($_GET["send"]) && $_GET["send"] == "complete")
		));

		/** コメントフォーム **/
		if($this->isInsertCommentForm && $this->isLoggedIn && $this->userId){
			$userLogic = SOY2Logic::createInstance("site_include.plugin.soyshop_login_check.logic.UserLogic", array("siteId" => $this->siteId, "userId" => $this->userId));
			$info = $userLogic->getAuthorInfo();
		}else{
			$info = array("author" => null, "mailAddress" => null, "url" => null);
		}
		$obj->addInput("author_login", array(
			"soy2prefix" => "cms",
			"name" => "author",
			"value" => $info["author"]
		));

		$obj->addInput("mail_address_login", array(
			"soy2prefix" => "cms",
			"name" => "mail_address",
			"value" => $info["mailAddress"]
		));

		$obj->addInput("url_login", array(
			"soy2prefix" => "cms",
			"name" => "url",
			"value" => $info["url"]
		));
	}

	//コメント投稿時のポイント付与
	function onSubmitComment($args){
		$entry = $args["page"]->entry;
		$entryComment = $args["entryComment"];

		//コメント文章があるかを念の為にチェック
		if(is_null($entryComment->getBody()) || strlen($entryComment->getBody()) === 0) return;

		//ポイント付与
		$pointLogic = SOY2Logic::createInstance("site_include.plugin.soyshop_login_check.logic.PointLogic", array("siteId" => $this->siteId, "point" => $this->point, "entry" => $entry));
		$pointLogic->addPoint();
	}

	function onCallCustomField(){

		$arg = SOY2PageController::getArguments();
		$entryId = (isset($arg[0])) ? (int)$arg[0] : null;

		return $this->buildForm($entryId);
	}

	function onCallCustomField_inBlog(){

		$arg = SOY2PageController::getArguments();
		$entryId = (isset($arg[1])) ? (int)$arg[1] : null;

		return $this->buildForm($entryId);
	}

	function buildForm($entryId){

		$attributeDao = SOY2DAOFactory::create("cms.EntryAttributeDAO");
		try{
			$field = $attributeDao->get($entryId, self::PLUGIN_ID);
		}catch(Exception $e){
			$field = new EntryAttribute();
		}

		$itemLogic = SOY2Logic::createInstance("site_include.plugin.soyshop_login_check.logic.ItemLogic", array("siteId" => $this->siteId));

		$html = array();
		$html[] = "<div class=\"section custom_field\">";
		$html[] = "<p class=\"sub\">";
		$html[] = "<label for=\"" . self::PLUGIN_ID . "_item\">アクセス許可商品</label>";
		$html[] = "</p>";
		$html[] = "<div style=\"margin:-0.5ex 0px 0.5ex 1em;\">";

		//カスタムフィールドの値から配列を生成
		$array = explode(",", $field->getValue());
		if(isset($array[0]) && strlen($array[0]) > 0) array_push($array, "");

		//SOY Shopに登録されている商品を取得して、ここに表示する
		foreach($array as $code){
			$html[] = "<select name=\"" . self::PLUGIN_ID . "[]\">";
			$html[] = "<option value=\"\"></option>";

			$items = $itemLogic->getItems();
			foreach($items as $item){
				if($code == $item->getCode()){
					$html[] = "<option value=\"" .$item->getCode() . "\" selected=\"selected\">" . $item->getName() . "(" . $item->getCode() . ")</option>";
				}else{
					$html[] = "<option value=\"" .$item->getCode() . "\">" . $item->getName() . "(" . $item->getCode() . ")</option>";
				}
			}

			$html[] = "</select><br>";
		}

		$html[] = "</div>";
		$html[] = "<div style=\"margin:-0.5ex 0px 0.5ex 1em;\">";

		try{
			$typeField = $attributeDao->get($entryId, self::PLUGIN_ID . "Type");
		}catch(Exception $e){
			$typeField = new EntryAttribute();
		}

		if($typeField->getValue() == "or" || is_null($typeField->getValue())){
			$html[] = "<label><input type=\"radio\" name=\"" . self::PLUGIN_ID . "Type\" value=\"or\" checked=\"checked\">どれか一つ購入</label> ";
		}else{
			$html[] = "<label><input type=\"radio\" name=\"" . self::PLUGIN_ID . "Type\" value=\"or\">どれか一つ購入</label> ";
		}

		if($typeField->getValue() == "and"){
			$html[] = "<label><input type=\"radio\" name=\"" . self::PLUGIN_ID . "Type\" value=\"and\" checked=\"checked\">すべて購入</label> ";
		}else{
			$html[] = "<label><input type=\"radio\" name=\"" . self::PLUGIN_ID . "Type\" value=\"and\">すべて購入</label> ";
		}

		$html[] = "</div>";
		$html[] = "</div>";

		return implode("\n", $html);
	}

	/**
	 * 記事作成時、記事更新時
	 */
	function onEntryUpdate($arg){
		$dao = SOY2DAOFactory::create("cms.EntryAttributeDAO");

		$entry = $arg["entry"];

		$arg = SOY2PageController::getArguments();
		$entryId = (isset($arg[0])) ? (int)$arg[0] : null;
		$postFields = (isset($_POST[self::PLUGIN_ID]) && count($_POST[self::PLUGIN_ID]) > 0) ? $_POST[self::PLUGIN_ID] : null;

		//値の整理
		for($i = 0; $i < count($postFields); $i++){
			if(strlen($postFields[$i]) === 0){
				unset($postFields[$i]);
			}
		}

		$values = implode(",", array_unique($postFields));

		$flag = false;

		//更新の場合
		try{
			$obj = $dao->get($entry->getId(), self::PLUGIN_ID);
			$obj->setValue($values);
			$dao->update($obj);
			$flag = true;
		}catch(Exception $e){
			//
		}

		if(!$flag){
			//新規作成の場合
			try{
				$obj = new EntryAttribute();
				$obj->setEntryId($entry->getId());
				$obj->setFieldId(self::PLUGIN_ID);
				$obj->setValue($values);
				$dao->insert($obj);
			}catch(Exception $e){
				//
			}
		}

		if(!isset($_POST[self::PLUGIN_ID . "Type"])) return;

		try{
			$dao->delete($entryId, self::PLUGIN_ID . "Type");
		}catch(Exception $e){

		}

		try{
			$obj = new EntryAttribute();
			$obj->setEntryId($entry->getId());
			$obj->setFieldId(self::PLUGIN_ID . "Type");
			$obj->setValue($_POST[self::PLUGIN_ID . "Type"]);
			$dao->insert($obj);
		}catch(Exception $e){
			//
		}
	}

	/**
	 * 記事複製時
	 */
	function onEntryCopy($args){
		list($old, $new) = $args;

		$dao = SOY2DAOFactory::create("cms.EntryAttributeDAO");
		$keys = array(self::PLUGIN_ID, self::PLUGIN_ID . "Type");

		foreach($keys as $key){
			try{
				$custom = $dao->get($old, $key);
			}catch(Exception $e){
				$custom = null;
			}

			if(isset($custom)){
				try{
					$obj = new EntryAttribute();
					$obj->setEntryId($new);
					$obj->setFieldId($custom->getFieldId());
					$obj->setValue($custom->getValue());
					$obj->setExtraValuesArray($custom->getExtraValues());
					$dao->insert($obj);
				}catch(Exception $e){

				}
			}
		}
	}

	/**
	 * 記事削除時
	 * @param array $args エントリーID
	 */
	function onEntryRemove($args){
		$dao = SOY2DAOFactory::create("cms.EntryAttributeDAO");
		foreach($args as $entryId){
			try{
				$dao->deleteByEntryId($entryId);
			}catch(Exception $e){
				//
			}
		}

		return true;
	}

	function config_page(){
		include_once(dirname(__FILE__)."/config/SOYShopLoginCheckConfigFormPage.class.php");
		$form = SOY2HTMLFactory::createInstance("SOYShopLoginCheckConfigFormPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	function checkRedirect($pageId){
		//既にログインしている場合はリダイレクトをしないを返す
		if($this->isLoggedIn) return 0;

		//プラグインのページ毎のリダイレクト設定を確認する
		return (isset($this->config_per_page[$pageId])) ? (int)$this->config_per_page[$pageId] : 0;
	}

	public static function register(){

		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(!$obj){
			$obj = new SOYShopLoginCheckPlugin();
		}

		CMSPlugin::addPlugin(self::PLUGIN_ID,array($obj,"init"));
	}

	function getSiteId(){
		return $this->siteId;
	}
	function setSiteId($siteId){
		$this->siteId = $siteId;
	}

	function getPoint(){
		return $this->point;
	}
	function setPoint($point){
		$this->point = $point;
	}

	function getIsInsertCommentForm(){
		return $this->isInsertCommentForm;
	}

	function setIsInsertCommentForm($isInsertCommentForm){
		$this->isInsertCommentForm = $isInsertCommentForm;
	}

	function getAllowBrowseEntryByPurchased(){
		return $this->allowBrowseEntryByPurchased;
	}

	function setAllowBrowseEntryByPurchased($allowBrowseEntryByPurchased){
		$this->allowBrowseEntryByPurchased = $allowBrowseEntryByPurchased;
	}

	function getDoRedirectAfterLogin(){
		return $this->doRedirectAfterLogin;
	}
	function setDoRedirectAfterLogin($doRedirectAfterLogin){
		$this->doRedirectAfterLogin = $doRedirectAfterLogin;
	}

	function getDoRedirectAfterRemind(){
		return $this->doRedirectAfterRemind;
	}
	function setDoRedirectAfterRemind($doRedirectAfterRemind){
		$this->doRedirectAfterRemind = $doRedirectAfterRemind;
	}
}
?>