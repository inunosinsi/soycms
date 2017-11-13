<?php

class TabPage extends CMSHTMLPageBase {

	/**
	 * 有効なタブとURLのパターン
	 */
	//シンプルモードのタブ
	private $activeTabRulesForSimpleMode = array(
			'Blog' => 'simple_blog',
			'Simple.Index' => 'simple_dashboard',
			'Simple.Entry'=> 'simple_entry',
			'Simple' => 'simple_dashboard',
	);
	//通常のタブ
	private $activeTabRules = array(
			'Index' => 'dashboard',
			'Page' =>	'page',
			'Entry'=> 'entry',
			'Label'=> 'label',
			'Blog' => 'page',
			'Plugin' => 'plugin',
			'Template' => 'page',
			'Module' => 'page',
			'FileManager' => 'file',
	);

	private $activeTab;
	private $isSimpleMode = false;

	function __construct() {
		$this->isSimpleMode = ! UserInfoUtil::hasSiteAdminRole();

		parent::__construct();

		//リクエストされたパスからActiveなパスを取得
		$requestPath = SOY2PageController::getRequestPath();

		if($this->isSimpleMode){
			foreach($this->activeTabRulesForSimpleMode as $uri => $tabId){
				if(strpos($requestPath, $uri) === 0){
					$this->activeTab = $tabId;
					break;
				}
			}
		}else{
			foreach($this->activeTabRules as $uri => $tabId){
				if(strpos($requestPath, $uri) === 0){
					$this->activeTab = $tabId;
					break;
				}
			}
		}
	}

	function execute(){

		parent::execute();

 		/* タブの状態を設定 */

		$this->addLink("dashboard",array(
			"link" => SOY2PageController::createLink(""),
			"class" => $this->getMenuStatus("dashboard")
		));

		$this->addLink("page",array(
			"link" => SOY2PageController::createLink("Page"),
			"class" => $this->getMenuStatus("page")
		));

		$this->addLink("entry",array(
			"link" => SOY2PageController::createLink("Entry"),
			"class" => $this->getMenuStatus("entry")
		));

		$this->addLink("label",array(
			"link" => SOY2PageController::createLink("Label"),
			"class" => $this->getMenuStatus("label")
		));

		$this->addLink("plugin",array(
			"link" => SOY2PageController::createLink("Plugin"),
			"class" => $this->getMenuStatus("plugin")
		));

		$this->addLink("file",array(
			"link" => SOY2PageController::createLink("FileManager"),
			"class" => $this->getMenuStatus("file")
		));

		/* シンプルモードのタブの状態を設定 */
		$this->addLink("simple_dashboard",array(
			"link" => SOY2PageController::createLink("Simple"),
			"class" => $this->getMenuStatus("simple_dashboard")
		));

		$this->addLink("simple_entry",array(
			"link" => SOY2PageController::createLink("Entry"),
			"class" => $this->getMenuStatus("simple_entry")
		));

		$this->addLink("simple_blog",array(
			"link" => SOY2PageController::createLink("Blog.List"),
			"class" => $this->getMenuStatus("simple_blog")
		));

		$this->addLink("simple_preview",array(
			"link" => SOY2PageController::createLink("Page.Preview"),
			"class" => $this->getMenuStatus("simple_preview")
		));

		/* タブの切り替えを行う */
		$this->createAdd("simple_mode","HTMLModel",array(
				"visible" => $this->isSimpleMode
		));

		$this->createAdd("not_simple_mode","HTMLModel",array(
				"visible" => ! $this->isSimpleMode
		));

		/* サイドバーの表示・非表示 */
		$hideSideMenu = ( isset($_COOKIE["soycms-hide-side-menu"]) && $_COOKIE["soycms-hide-side-menu"] == "true" );
		$this->addModel("toggle-arrow", array(
				"class" => $hideSideMenu ? "fa fa-fw fa-angle-right" : "fa fa-fw fa-angle-left",
		));

		/* スマホ用のUpperMenu同等 */
		$this->addLabel("adminname", array(
				"text" => UserInfoUtil::getUserName(),
				"width" => 18,
				"title" => UserInfoUtil::getUserName(),
		));

		$this->addLink("account_link", array(
				"link" => (defined("SOYCMS_ASP_MODE")) ?
				SOY2PageController::createLink("Login.UserInfo")
				:SOY2PageController::createRelativeLink("../admin/index.php/Account")
		));

		//CMS管理へのリンク
		$this->createAdd("admin_link","HTMLLink",array(
				"link" => SOY2PageController::createRelativeLink("../admin/"),
		));
		$this->addModel("show_admin_link","HTMLLink",array(
				"visible" => !defined("SOYCMS_ASP_MODE") && !UserInfoUtil::hasOnlyOneRole()
		));

		//キャッシュ削除（処理はUpperMenuPage）
		$site = UserInfoUtil::getSite();
		$param = UpperMenuPage::PARAM_KEY_CLEAR_CACHE . "&". UpperMenuPage::PARAM_KEY_TARGET_SITE ."=" . $site->getSiteId() . ( strlen($_SERVER['QUERY_STRING']) ? "&".$_SERVER['QUERY_STRING'] : "");
		$this->addActionLink("delete_cache_link",array(
				"link" => "?".$param,
		));


		//AppのDBをサイト内に置く場合のメニュー
		$this->displayAppLink();

	}

	/**
	 * AppのDBをサイト内に置く場合のメニュー
	 */
	private function displayAppLink(){
		//SOY InquiryかSOY Mailのデータベースがサイト側に存在している場合、リンクを表示する

		$inquiryUseSiteDb = SOYAppUtil::checkAppAuth("inquiry");
		$mailUseSiteDb = SOYAppUtil::checkAppAuth("mail");

		$this->addModel("display_inquiry_link", array(
				"visible" => ($inquiryUseSiteDb)
		));

		$this->addModel("display_mail_link", array(
				"visible" => ($mailUseSiteDb)
		));

		//SOY Inquiryのデータベースがサイト側に存在する場合に表示するリンク
		$this->addLink("inquiry_link", array(
				"link" => SOYAppUtil::createAppLink("inquiry")
		));

		//SOY Mailのデータベースがサイト側に存在する場合に表示するリンク
		$this->addLink("mail_link", array(
				"link" => SOYAppUtil::createAppLink("mail")
		));
	}

	/**
	 * メニューの状態を設定
	 */
	private function getMenuStatus($tabName){
		if($tabName == $this->activeTab){
			return "active";
		}else{
			return "";
		}
	}
}
