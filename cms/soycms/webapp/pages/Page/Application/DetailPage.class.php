<?php

class DetailPage extends CMSWebPageBase{

	var $id;

	function doPost(){
		if(soy2_check_token()){
			//ApplicationIdの更新
			if(isset($_POST["applicationId"])){
				$applicationId = $_POST["applicationId"];
				$pageConfig = (isset($_POST["PageConfig"])) ? (object)$_POST["PageConfig"] : new stdClass;
				$pageDao = SOY2DAOFactory::create("cms.ApplicationPageDAO");
				$applicationPage = $pageDao->getById($this->id);
				$applicationPage->setApplicationId($applicationId);

				SOY2::cast($applicationPage,$pageConfig);

				$pageDao->updatePageConfig($applicationPage);
			}

			//更新は通常ページと同じ
			$result = $this->run("Page.UpdateAction",array(
				"id" => $this->id
			));

			if($result->success()){
				$this->addMessage("PAGE_UPDATE_SUCCESS");
			}else{
				$this->addErrorMessage("PAGE_UPDATE_FAILED");
			}

			$this->jump("Page.Detail.".$this->id);
			exit;
		}
	}

	function __construct($arg){


		$this->id = @$arg[0];

		parent::__construct();

		//新規作成してから来たときのメッセージ表示
		if(isset($_GET["msg"]) && $_GET["msg"] == "create"){
			$this->addMessage("PAGE_CREATE_SUCCESS");
			$this->jump("Page.Detail.".$this->id);
		}

		if(is_null($this->id)){
			$this->jump("Page");
		}

		$id = $this->id;


		$page = $this->getPageObject($id);

		//アプリケーションじゃなかった場合はページ詳細へ飛ばす(オブジェクトの取得の失敗の場合も加味する)
		if(!method_exists($page, "getPageType") || $page->getPageType() != Page::PAGE_TYPE_APPLICATION){
			$this->jump("Page.Detail.".$id);
		}

		$this->createAdd("title","HTMLInput",array(
			"value"=>$page->getTitle(),
			"name"=>"title"
		));

		$this->createAdd("uri","HTMLInput",array(
			"value"=>$page->getUri(),
			"name"=>"uri"
		));

		$this->createAdd("page_icon_show","HTMLImage",array(
			"src" => $page->getIconUrl(),
			"onclick" => "javascript:changeImageIcon(".$page->getId().");"
		));

		$this->createAdd("page_icon","HTMLInput",array(
			"value"=>$page->getIcon()
		));

		$this->createAdd("title_format","HTMLInput",array(
			"value"=>$page->getPageTitleFormat(),
			"name"=>"PageConfig[pageTitleFormat]"
		));


		$this->createAdd("uri_prefix","HTMLLabel",array(
			"text"=>$this->getURIPrefix($id)
		));

		$this->createAdd("parent_page","HTMLSelect",array(
			"selected"=>$page->getParentPageId(),
			"options"=>$this->getPageList(),
			"indexOrder"=>true,
			"name"=>"parentPageId"
		));

		//CSS保存のボタン
		$this->createAdd("save_css_button", "HTMLModel", array(
			"visible" => function_exists("json_encode")
		));

		//template保存のボタン追加
		$this->createAdd("save_template_button","HTMLModel",array(
			"id" => "save_template_button",
			"onclick" => "javascript:save_template('".SOY2PageController::createLink("Page.Editor.SaveTemplate." . $page->getId())."',$(this));",
			"visible" => function_exists("json_encode")
		));

		$this->createAdd("template","HTMLTextArea",array(
			"text"=>$page->getTemplate(),
			"name"=>"template"
		));

		$this->createAdd("template_editor","HTMLModel",array(
				//"_src"=>SOY2PageController::createRelativeLink("./webapp/pages/files/vendor/soycms/template-editor/template-editor.html"),
				"_src"=>SOY2PageController::createRelativeLink("./js/editor/template_editor.html"),
		));

		$this->createAdd("state_draft","HTMLCheckBox",array(
			"selected"=>!$page->getIsPublished(),
			"name"=>"isPublished",
			"value"=>0,
			"label"=>CMSMessageManager::get("SOYCMS_DRAFT")
		));
		$this->createAdd("state_public","HTMLCheckBox",array(
			"selected"=>$page->getIsPublished(),
			"name"=>"isPublished",
			"value"=>1,
			"label"=>CMSMessageManager::get("SOYCMS_PUBLISHED")
		));

		$start = $page->getOpenPeriodStart();
		$end   = $page->getOpenPeriodEnd();


		//公開期間フォームの表示
		$this->createAdd("start_date","HTMLInput",array(
			"value"=>(is_null($start)) ? "" : date('Y-m-d H:i:s',$start),
			"name"=>"openPeriodStart"
		));
		$this->createAdd("end_date","HTMLInput",array(
			"value"=>(is_null($end)) ? "" : date('Y-m-d H:i:s',$end),
			"name"=>"openPeriodEnd"
		));

		$this->createAdd("open_period_show","HTMLLabel",array(
			"html" => CMSUtil::getOpenPeriodMessage($start, $end)
		));
		$this->addScript("PanelManager.js",array(
			"src" => SOY2PageController::createRelativeLink("./js/cms/PanelManager.js")
		));

		$this->addScript("TemplateEditor",array(
			"src" => SOY2PageController::createRelativeLink("./js/editor/template_editor.js")
		));

		HTMLHead::addLink("editor",array(
			"rel" => "stylesheet",
			"type" => "text/css",
			"href" => SOY2PageController::createRelativeLink("./css/editor/editor.css")
		));

		HTMLHead::addLink("section",array(
			"rel" => "stylesheet",
			"type" => "text/css",
			"href" => SOY2PageController::createRelativeLink("./css/form.css")
		));

		HTMLHead::addLink("form",array(
			"rel" => "stylesheet",
			"type" => "text/css",
			"href" => SOY2PageController::createRelativeLink("./js/cms/PanelManager.css")
		));




		$this->createAdd("page_detail_form","HTMLForm",array(
			"name" => "main_form"
		));

		//ブロック
		$this->createAdd("page_block_info","Block.BlockListPage",array(
			"pageId" => $id
		));

		//ブロック
		$this->createAdd("virtual_tree","HTMLModel",array(
			"src"=>SOY2PageController::createLink("Page.Mobile.TreePage")."/".$id
		));


		//見出しに現在編集しているページ名を表示
		$this->createAdd("page_name","HTMLLabel",array("text"=>$page->getTitle()));
		$this->addScript("cssmenu",array(
				"src" => SOY2PageController::createRelativeLink("js/editor/cssMenu.js")
			));

		//CSS保存先URLをJavaScriptに埋め込みます
		$this->addScript("cssurl",array(
			"script"=>'var cssURL = "'.SOY2PageController::createLink("Page.Editor").'";' .
					  'var siteId="'.UserInfoUtil::getSite()->getSiteId().'";' .
					  //'var editorLink = "'.SOY2PageController::createLink("Page.Editor").'";'.
					  'var siteURL = "'.UserInfoUtil::getSiteUrl().'";'
		));

		$this->addLink("insertLink",array(
				"link" => SOY2PageController::createLink("Page.Editor.InsertLink"),
		));
		$this->addLink("fileUpload",array(
				"link" => SOY2PageController::createLink("Page.Editor.FileUpload"),
		));

		//絵文字入力用
		$this->addScript("mceSOYCMSEmojiURL",array(
			"script" => 'var mceSOYCMSEmojiURL = "'.SOYCMSEmojiUtil::getEmojiInputPageUrl().'";',
			"visible" => SOYCMSEmojiUtil::isInstalled(),
		));

		//アイコンリスト
		$this->createAdd("image_list","LabelIconList",array(
			"list" => $this->getLabelIconList()
		));

		//ファイルツリーをつかいます。
		CMSToolBox::enableFileTree();

		CMSToolBox::addLink(CMSMessageManager::get("SOYCMS_CREATE_NEW_WEBPAGE"),SOY2PageController::createLink("Page.Create"),true);
		CMSToolBox::addLink($this->getMessage("SOYCMS_TEMPLATE_HISTORY"),SOY2PageController::createLink("Page.TemplateHistory.".$this->id),true);
		CMSToolBox::addLink($this->getMessage("SOYCMS_DYNAMIC_EDIT"),SOY2PageController::createLink("Page.Preview.".$this->id),false,"this.target = '_blank'");
		if($page->isActive() == Page::PAGE_ACTIVE){
			CMSToolBox::addLink($this->getMessage("SOYCMS_SHOW_WEBPAGE"),CMSUtil::getSiteUrl().$page->getUri(),false,"this.target = '_blank'");
		}
		CMSToolBox::addLink($this->getMessage("SOYCMS_DOWNLOAD_TEMPLATE"),SOY2PageController::createLink("Page.ExportTemplate.".$this->id),false);

		CMSToolBox::addPageJumpBox();

		//短縮URLのフィールドの呼び出し
		//url_shortener_display, url_shortener_input
		$this->addModel("url_shortener_display", array(
			"visible" => CMSPlugin::activeCheck("UrlShortener"),
		));
		CMSPlugin::callLocalPluginEventFunc("onPageEdit","UrlShortener",array("page" => $this));


		/* こっからApplicationページのちょっと違うところ */
		$applicationPage = SOY2DAOFactory::create("cms.ApplicationPageDAO")->getById($id);
		$appId = $applicationPage->getApplicationId();
		if(is_null($appId))$appId = "?";	//不可能な文字列
		$logic = SOY2Logic::createInstance("logic.admin.Application.ApplicationLogic");

		//Dsn書き換え開始
		$oldDsn = SOY2DAOConfig::Dsn();

		SOY2DAOConfig::Dsn(ADMIN_DB_DSN);
		$applications = $logic->getApplications();
		if(UserInfoUtil::isDefaultUser()){
			$loginableApplications = $applications;
		}else{
			$loginableApplications = $logic->getLoginableApplications(UserInfoUtil::getUserId());
		}

		//戻す
		SOY2DAOConfig::Dsn($oldDsn);

		$options = array();
		foreach($loginableApplications as $key => $array){
			$options[$key] = $array["title"];
		}

		//ログインできなかった場合も表示しないといけない
		$hasLoginable = true;
		if(!isset($options[$appId]) && isset($applications[$appId])){
			$options[$appId] = $applications[$appId]["title"];
		}

		$applicationList = $this->create("application_list","HTMLSelect",array(
			"name" => "applicationId",
			"options" => $options,
			"selected" => $appId
		));
		if(!$hasLoginable)$applicationList->setAttribute("disabled","disabled");
		$this->add("application_list",$applicationList);

		$this->createAdd("application_link","HTMLLink",array(
			"link" => SOY2PageController::createRelativeLink("../app/index.php/" . $appId),
			"visible" => $hasLoginable
		));

	}

	/**
	 * このページIDに対する呼び出しURIの定型部分を取得
	 */
	function getURIPrefix($pageId){
		return CMSUtil::getSiteUrl();
	}

	/**
	 * IDに対するページオブジェクトを取得する
	 */
	function getPageObject($id){
		return SOY2ActionFactory::createInstance("Page.DetailAction",array(
			"id" => $id
		))->run()->getAttribute("Page");
	}

	/**
	 * ページIDをキーとするリストを取得
	 */
	function getPageList(){
		return SOY2ActionFactory::createInstance("Page.PageListAction",array(
			"buildTree" => true
		))->run()->getAttribute("PageTree");
	}

	/**
	 * ページに使えるアイコンの一覧を返す
	 */
	function getLabelIconList(){

		$dir = CMS_PAGE_ICON_DIRECTORY;

		$files = scandir($dir);

		$return = array();

		foreach($files as $file){
			if($file[0] == ".")continue;

			if(!preg_match('/^page_/',$file))continue;

			$return[] = (object)array(
				"filename" => $file,
				"url" => CMS_PAGE_ICON_DIRECTORY_URL . $file,
			);
		}


		return $return;
	}


}

class LabelIconList extends HTMLList{

	protected function populateItem($entity){
		$this->createAdd("image_list_icon","HTMLImage",array(
			"src" => $entity->url,
			"ondblclick" => "javascript:setChangeLabelIcon('".$entity->filename."','".$entity->url."');"
		));
	}
}
