<?php

class DetailPage extends CMSWebPageBase{

	private $id;

	function doPost(){

		if(soy2_check_token()){
			$result = $this->run("Page.UpdateAction",array(
				"id" => $this->id,
				"updateConfig" => true
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
		if(!isset($arg[0])) $this->jump("Page");
		$this->id = (int)$arg[0];

		parent::__construct();

		//新規作成してから来たときのメッセージ表示
		if(isset($_GET["msg"]) && $_GET["msg"] == "create"){
			$this->addMessage("PAGE_CREATE_SUCCESS");
			$this->jump("Page.Detail.".$this->id);
		}

		$page = $this->getPageObject($this->id);

		switch($page->getPageType()){
			case Page::PAGE_TYPE_BLOG:	//ブログだった場合はブログページへ
				$this->jump("Blog." . $this->id);
				break;
			case Page::PAGE_TYPE_MOBILE:	//mobileページだった時はそっちに
				$this->jump("Page.Mobile.Detail." . $this->id);
				break;
			case Page::PAGE_TYPE_APPLICATION:	//applicationページだった場合
				$this->jump("Page.Application.Detail." . $this->id);
				break;
			case Page::PAGE_TYPE_ERROR:	//404ページだった場合の処理
				DisplayPlugin::hide("openperiod_section");
				break;
			default:
				DisplayPlugin::hide("error_submit_button");
		}

		$this->addInput("title", array(
			"value" => $page->getTitle(),
			"name" => "title"
		));

		$this->addInput("uri", array(
			"value" => $page->getUri(),
			"name" => "uri"
		));

		$this->addImage("page_icon_show", array(
			"src" => $page->getIconUrl(),
			"onclick" => "javascript:changeImageIcon(".$page->getId().");"
		));

		$this->addInput("page_icon", array(
			"value" => $page->getIcon()
		));

		$this->addInput("title_format", array(
			"value" => $page->getPageTitleFormat(),
			"name" => "pageTitleFormat"
		));


		$this->addLabel("uri_prefix", array(
			"text" => $this->getURIPrefix($this->id)
		));

		$this->addSelect("parent_page", array(
			"selected" => $page->getParentPageId(),
			"options" => $this->getPageList(),
			"indexOrder" => true,
			"name" => "parentPageId"
		));

		//CSS保存のボタン
		$this->addLabel("save_css_button", array(
			"visible" => function_exists("json_encode")
		));

		//template保存のボタン追加
		$this->addModel("save_template_button", array(
			"id" => "save_template_button",
			"onclick" => "javascript:save_template('".SOY2PageController::createLink("Page.Editor.SaveTemplate." . $page->getId())."',this);",
			"visible" => function_exists("json_encode")
		));

		$this->addTextArea("template", array(
			"text" => $page->getTemplate(),
			"name" => "template"
		));

		$this->addModel("template_editor", array(
				//"_src"=>SOY2PageController::createRelativeLink("./webapp/pages/files/vendor/soycms/template-editor/template-editor.html"),
				"_src"=>SOY2PageController::createRelativeLink("./js/editor/template_editor.html"),
		));

		$this->addCheckBox("state_draft", array(
			"selected"=>!$page->getIsPublished(),
			"name" => "isPublished",
			"value" => 0,
			"label" => $this->getMessage("SOYCMS_DRAFT")
		));
		$this->addCheckBox("state_public", array(
			"selected" => $page->getIsPublished(),
			"name" => "isPublished",
			"value" => 1,
			"label" => $this->getMessage("SOYCMS_PUBLISHED")
		));

		$start = $page->getOpenPeriodStart();
		$end   = $page->getOpenPeriodEnd();


		//公開期間フォームの表示
		$this->addInput("start_date", array(
			"value"=>(is_null($start)) ? "" : date('Y-m-d H:i:s',$start),
			"name" => "openPeriodStart"
		));
		$this->addInput("end_date", array(
			"value"=>(is_null($end)) ? "" : date('Y-m-d H:i:s',$end),
			"name" => "openPeriodEnd"
		));

		$this->addLabel("open_period_show", array(
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

		HTMLHead::addLink("form", array(
			"rel" => "stylesheet",
			"type" => "text/css",
			"href" => SOY2PageController::createRelativeLink("./js/cms/PanelManager.css")
		));


		$this->addForm("page_detail_form", array(
			"name" => "main_form"
		));

		//ブロック
		$this->createAdd("page_block_info","Block.BlockListPage",array(
			"pageId" => $this->id
		));

		//見出しに現在編集しているページ名を表示
		$this->addLabel("page_name", array(
			"text" => $page->getTitle()
		));
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

		$this->addModel("is_emoji_enabled",array(
			"visible" => SOYCMSEmojiUtil::isInstalled(),
		));

		//アイコンリスト
		$this->createAdd("image_list","_component.Label.LabelIconListComponent",array(
			"list" => $this->getLabelIconList()
		));


		//ファイルツリーをつかいます。
		CMSToolBox::enableFileTree();

		CMSToolBox::addLink($this->getMessage("SOYCMS_CREATE_NEW_WEBPAGE"),SOY2PageController::createLink("Page.Create"),true);
		CMSToolBox::addLink($this->getMessage("SOYCMS_TEMPLATE_HISTORY"),SOY2PageController::createLink("Page.TemplateHistory.".$this->id),true);
		CMSToolBox::addLink($this->getMessage("SOYCMS_DYNAMIC_EDIT"),SOY2PageController::createLink("Page.Preview.".$this->id),false,"this.target = '_blank'");
		if($page->isActive() == Page::PAGE_ACTIVE){
			CMSToolBox::addLink($this->getMessage("SOYCMS_SHOW_WEBPAGE"),CMSUtil::getSiteUrl().$page->getUri(),false,"this.target = '_blank'");
		}
		CMSToolBox::addLink($this->getMessage("SOYCMS_DOWNLOAD_TEMPLATE"),SOY2PageController::createLink("Page.ExportTemplate.".$this->id),false);
		if(CMSUtil::isPageTemplateEnabled()){
			CMSToolBox::addLink($this->getMessage("SOYCMS_APPLY_WEBPAGE_TEMPLATEPACK"),SOY2PageController::createLink("Page.ApplyTemplate.".$page->getId()),true);
		}
		CMSToolBox::addPageJumpBox();


		//短縮URLのフィールドの呼び出し
		//url_shortener_display, url_shortener_input
		CMSPlugin::callLocalPluginEventFunc("onPageEdit","UrlShortener",array("page" => $this));

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

	function getId(){
		return $this->id;
	}
}
