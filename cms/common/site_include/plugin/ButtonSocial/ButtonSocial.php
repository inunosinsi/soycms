<?php
/**
 * @ToDo 後日、iframe周りのhttpsの読み込みの対応を行う
 */
class ButtonSocialPlugin{

	private $logic;
	private $app_id;
	private $mixi_check_key;
	private $mixi_like_key;
	private $admins;
	private $description;
	private $image;
	private $fb_app_ver = "v2.10";

	//twitter card
	private $tw_card;	//cardの種類
	private $tw_id;	//twitterのID

	//fb_rootの表示設定
	//Array<ページID => 0 | 1> fb_rootを表示するは1
	public $config_per_page = array();
	//Array<ページID => Array<ページタイプ => 0 | 1>> fb_rootを表示するは1
	public $config_per_blog = array();

	function init(){
		SOY2::import("site_include.plugin.ButtonSocial.util.ButtonSocialUtil");

		CMSPlugin::addPluginMenu($this->getId(),array(
			"name"=>"ソーシャルボタン設置プラグイン",
			"description"=>"ページにソーシャルボタンを設置します。",
			"author"=>"齋藤毅",
			"url"=>"https://saitodev.co/soycms/",
			"mail"=>"tsuyoshi@saitodev.co",
			"version"=>"1.5.1"
		));

		CMSPlugin::addPluginConfigPage($this->getId(),array(
			$this,"config_page"
		));

		if(CMSPlugin::activeCheck($this->getId())){
			SOY2::import("site_include.plugin.ButtonSocial.util.ButtonSocialUtil");

			//公開画面側
			if(defined("_SITE_ROOT_")){
				CMSPlugin::setEvent('onEntryOutput',$this->getId(),array($this,"display"));

				//公開側のページを表示させたときに、メタデータを表示する
				CMSPlugin::setEvent('onPageOutput',$this->getId(),array($this,"onPageOutput"));
				CMSPlugin::setEvent('onOutput',$this->getId(),array($this,"onOutput"));
			}else{
				CMSPlugin::setEvent('onEntryUpdate', $this->getId(), array($this, "onEntryUpdate"));
				CMSPlugin::setEvent('onEntryCreate', $this->getId(), array($this, "onEntryUpdate"));
				CMSPlugin::setEvent('onEntryCopy', $this->getId(), array($this, "onEntryCopy"));
				CMSPlugin::setEvent('onEntryRemove', $this->getId(), array($this, "onEntryRemove"));

				SOY2::import("site_include.plugin.ButtonSocial.component.SocialCustomFieldForm");
				CMSPlugin::addCustomFieldFunction($this->getId(), "Entry.Detail", array($this, "onCallCustomField"));
				CMSPlugin::addCustomFieldFunction($this->getId(), "Blog.Entry", array($this, "onCallCustomField_inBlog"));
			}
		}else{
			//何もしない
		}
	}

	function getId(){
		return ButtonSocialUtil::PLUGIN_ID;
	}

	function display($arg){
		$entryId = $arg["entryId"];
		$htmlObj = $arg["SOY2HTMLObject"];

		list($url,$title) = ButtonSocialUtil::getDetailUrlAndTitle($htmlObj, $entryId);

		$btnLogic = SOY2Logic::createInstance("site_include.plugin.ButtonSocial.logic.BuildButtonLogic");

		$fbBtn = (strlen($this->app_id)) ? $btnLogic->buildFbButton($this->app_id,$url) : "";
		$htmlObj->addLabel("facebook_like_button", array(
			"soy2prefix" => "cms",
			"html" => $fbBtn
		));

		$htmlObj->addLabel("twitter_button", array(
			"soy2prefix" => "cms",
			"html" => $btnLogic->buildTwitterButton($url)

		));
		$htmlObj->addLabel("hatena_button", array(
			"soy2prefix" => "cms",
			"html" => $btnLogic->buildHatenaButton($url)
		));

		$htmlObj->addLink("mixi_check_button", array(
			"soy2prefix" => "cms",
			"link" => "http://mixi.jp/share.pl",
			"attr:class" => "mixi-check-button",
			"attr:data-key" => $this->mixi_check_key,
			"attr:data-url" => $url
		));

		$htmlObj->addLabel("mixi_check_script", array(
			"soy2prefix" => "cms",
			"html" => (strlen($this->mixi_like_key)) ? $btnLogic->buildMixiCheckScript() : ""
		));

		$htmlObj->addLabel("mixi_like_button", array(
			"soy2prefix" => "cms",
			"html" => (strlen($this->mixi_like_key)) ? $btnLogic->buildMixiLikeButton($this->mixi_like_key) : ""
		));

		$htmlObj->addLabel("pocket_button", array(
			"soy2prefix" => "cms",
			"html" => $btnLogic->buildPocketButton()
		));

		//廃止されたものをタグだけ残しておく
		foreach(array("twitter_button_mobile", "mixi_check_button_mobile", "mixi_like_button_mobile", "google_plus_button") as $t){
			$htmlObj->addLabel($t, array("soy2prefix" => "cms","html" => ""));
		}
	}

	function onPageOutput($obj){
		$entryId = (get_class($obj) == "CMSBlogPage" && isset($obj->entry) && !is_null($obj->entry->getId())) ? (int)$obj->entry->getId() : null;

		$metaLogic = SOY2Logic::createInstance("site_include.plugin.ButtonSocial.logic.BuildMetaLogic");

		$ogMeta = $metaLogic->buildOgMeta($obj, $this->description, $this->image, $entryId);
		$obj->addLabel("og_meta", array(
			"soy2prefix" => "sns",
			"html" => $ogMeta
		));

		$obj->addLabel("twitter_card_meta", array(
			"soy2prefix" => "sns",
			"html" => (strlen($this->tw_card)) ? $metaLogic->buildTwitterCardMeta($obj, $this->tw_card, $this->tw_id, $this->description, $this->image, $entryId) : ""
		));

		$fbMeta = (strlen($this->app_id) && strlen($this->admins)) ? $metaLogic->buildFbMeta($this->app_id, $this->admins) : "";
		$obj->addLabel("facebook_meta", array(
			"soy2prefix" => "sns",
			"html" => $fbMeta
		));

		$btnLogic = SOY2Logic::createInstance("site_include.plugin.ButtonSocial.logic.BuildButtonLogic");
		$fbBtn = (strlen($this->app_id)) ? $btnLogic->buildFbButton($this->app_id) : "";
		$obj->addLabel("facebook_like_button", array(
			"soy2prefix" => "sns",
			"html" => $fbBtn
		));

		$twBtn = $btnLogic->buildTwitterButton();
		$obj->addLabel("twitter_button", array(
			"soy2prefix" => "sns",
			"html" => $twBtn
		));

		$obj->addLabel("twitter_button_mobile", array(
			"soy2prefix" => "sns",
			"html" => $twBtn
		));

		$hatenaBtn = $btnLogic->buildHatenaButton();
		$obj->addLabel("hatena_button", array(
			"soy2prefix" => "sns",
			"html" => $hatenaBtn
		));

		$obj->addLabel("pocket_button", array(
			"soy2prefix" => "sns",
			"html" => $btnLogic->buildPocketButton()
		));

		/*
		 * 互換性のため block:id のものも置いておく
		 */
		$obj->addLabel("og_meta", array(
			"soy2prefix" => "block",
			"html" => $ogMeta
		));
		$obj->addLabel("facebook_meta", array(
			"soy2prefix" => "block",
			"html" => $fbMeta
		));
		$obj->addLabel("facebook_like_button", array(
			"soy2prefix" => "block",
			"html" => $fbBtn
		));
		$obj->addLabel("twitter_button", array(
			"soy2prefix" => "block",
			"html" => $twBtn
		));
		$obj->addLabel("hatena_button", array(
			"soy2prefix" => "block",
			"html" => $hatenaBtn
		));

		//廃止
		$obj->addLabel("google_plus_button", array(
			"soy2prefix" => "sns",
			"html" => ""
		));
	}

	function onOutput($arg){
		$html = &$arg["html"];

		//ダイナミック編集では挿入しない
		if(defined("CMS_PREVIEW_MODE") && CMS_PREVIEW_MODE){
			return $html;
		}

		//app_idが入力されていない場合は表示しない
		if(is_null($this->app_id) || strlen($this->app_id) === 0){
			return $html;
		}

		//ページの時のチェック
		if(isset($this->config_per_page[$arg["page"]->getId()]) && $this->config_per_page[$arg["page"]->getId()] != 1){
			return $html;
		}

		//ブログページの時のチェック
		if($arg["page"]->getPageType() == Page::PAGE_TYPE_BLOG){
			if(isset($this->config_per_blog[$arg["page"]->getId()][$arg["webPage"]->mode]) && $this->config_per_blog[$arg["page"]->getId()][$arg["webPage"]->mode] != 1){
				return $html;
			}
		}

		$metaLogic = SOY2Logic::createInstance("site_include.plugin.ButtonSocial.logic.BuildMetaLogic");
		if(stripos($html,'<body>') !== false){
			$html = str_ireplace('<body>', '<body>' . "\n" . $metaLogic->buildFbRoot($this->app_id), $html);
		}elseif(preg_match('/<body\\s[^>]+>/',$html)){
			$html = preg_replace('/(<body\\s[^>]+>)/', "\$0\n" . $metaLogic->buildFbRoot($this->app_id), $html);
		}else{
			//何もしない
		}

		return $html;
	}

	function onEntryUpdate($arg){
		$entry = $arg["entry"];
		$attr = ButtonSocialUtil::getAttr($entry->getId());
		$v = (isset($_POST[ButtonSocialUtil::PLUGIN_KEY]) && strlen($_POST[ButtonSocialUtil::PLUGIN_KEY]) > 0) ? trim($_POST[ButtonSocialUtil::PLUGIN_KEY]) : "";
		$attr->setValue($v);

		ButtonSocialUtil::saveAttr($attr);
	}

	function onEntryCopy($args){
		list($old, $new) = $args;
		$attr = ButtonSocialUtil::getAttr($old);
		$attr->setEntryId($new);
		ButtonSocialUtil::saveAttr($attr);

		return true;
	}

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

	function onCallCustomField(){
		$arg = SOY2PageController::getArguments();
		$entryId = (isset($arg[0])) ? (int)$arg[0] : null;
		return SocialCustomFieldForm::buildForm($entryId);
	}

	function onCallCustomField_inBlog(){
		$arg = SOY2PageController::getArguments();
		$entryId = (isset($arg[1])) ? (int)$arg[1] : null;
		return SocialCustomFieldForm::buildForm($entryId);
	}

	function config_page($message){
		include(dirname(__FILE__) . "/config/ButtonSocialConfigFormPage.class.php");
		$form = SOY2HTMLFactory::createInstance("ButtonSocialConfigFormPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	function getTwCard(){
		return $this->tw_card;
	}
	function setTwCard($tw_card){
		$this->tw_card = $tw_card;
	}

	function getTwId(){
		return $this->tw_id;
	}
	function setTwId($tw_id){
		$this->tw_id = $tw_id;
	}

	function getAppId(){
		return $this->app_id;
	}
	function setAppId($app_id){
		$this->app_id = $app_id;
	}

	function getMixiCheckKey(){
		return $this->mixi_check_key;
	}
	function setMixiCheckKey($mixi_check_key){
		$this->mixi_check_key = $mixi_check_key;
	}

	function getMixiLikeKey(){
		return $this->mixi_like_key;
	}
	function setMixiLikeKey($mixi_like_key){
		$this->mixi_like_key = $mixi_like_key;
	}

	function getAdmins(){
		return $this->admins;
	}
	function setAdmins($admins){
		$this->admins = $admins;
	}

	function getDescription(){
		return $this->description;
	}
	function setDescription($description){
		$this->description = $description;
	}

	function getImage(){
		return $this->image;
	}
	function setImage($image){
		$this->image = $image;
	}

	function getFbAppVer(){
		return $this->fb_app_ver;
	}
	function setFbAppVer($v){
		$this->fb_app_ver = $v;
	}


	public static function register(){
		SOY2::import("site_include.plugin.ButtonSocial.util.ButtonSocialUtil");
		$obj = CMSPlugin::loadPluginConfig(ButtonSocialUtil::PLUGIN_ID);
		if(is_null($obj)){
			$obj = new ButtonSocialPlugin();
		}
		CMSPlugin::addPlugin(ButtonSocialUtil::PLUGIN_ID,array($obj,"init"));
	}
}
ButtonSocialPlugin::register();
