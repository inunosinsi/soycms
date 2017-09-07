<?php

class TemplatePage extends CMSWebPageBase{

	var $id;
	var $mode;

	function doPost(){
		if(soy2_check_token()){

			$result = $this->run("Blog.UpdateTemplateAction",array(
				"id"=>$this->id,
				"mode"=>$this->mode
			));

			if($result == SOY2ACtion::SUCCESS){
				$this->addMessage("BLOG_TEMPLATE_UPDATE_SUCCESS");
			}else{
				$this->addErrorMessage("BLOG_TEMPLATE_UPDATE_FAILED");
			}

		}

		$this->jump("Blog.Template.".$this->id.".".$this->mode);
	}

	function __construct($args) {

		$this->id = $args[0];
		$this->mode = @$args[1];

		if(!$this->mode)$this->mode = "top";

		parent::__construct();

		$result = $this->run("Blog.DetailAction",array("id"=>$this->id));
		if(!$result->success()){
			$this->addMessage("PAGE_DETAIL_GET_FAILED");
			$this->jump("Page");
			exit;
		}

		$page = $result->getAttribute("Page");

		//テンプレート別の動作
		switch($this->mode){
			case "entry":
				$templateTypeText = CMSMessageManager::get("SOYCMS_ENTRY");
				$template = $page->getEntryTemplate();
				break;
			case "popup":
				$templateTypeText = CMSMessageManager::get("SOYCMS_POPUPCOMMENT");
				$template = $page->getPopUpTemplate();
				break;
			case "top":
				$templateTypeText = CMSMessageManager::get("SOYCMS_BLOG_TOPPAGE");
				$template = $page->getTopTemplate();
				break;
			case "archive":
			default:
				$templateTypeText = CMSMessageManager::get("SOYCMS_BLOG_ARCHIVEPAGE");
				$template = $page->getArchiveTemplate();
		}

		//ブログメニュー
		$this->createAdd("BlogMenu","Blog.BlogMenuPage",array(
			"arguments" => array($this->id)
		));

		//テンプレートの種類を選択
		$this->createAdd("template_type","HTMLLabel",array(
			"text" => $templateTypeText
		));

		//ブログ名
		$this->createAdd("blog_name","HTMLLabel",array(
			"text" => $page->getTitle()
		));

		//Editorの読み込み
		$this->addScript("TemplateEditor",array(
			"src" => SOY2PageController::createRelativeLink("./js/editor/template_editor.js")
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
					  'var editorLink = "'.SOY2PageController::createLink("Page.Editor").'";'.
					  'var siteURL = "'.UserInfoUtil::getSiteUrl().'";'
		));

		//絵文字入力用
		$this->addScript("mceSOYCMSEmojiURL",array(
			"script" => 'var mceSOYCMSEmojiURL = "'.SOYCMSEmojiUtil::getEmojiInputPageUrl().'";',
			"visible" => SOYCMSEmojiUtil::isInstalled(),
		));

		HTMLHead::addLink("editorcss",array(
				"rel" => "stylesheet",
				"type" => "text/css",
				"href" => SOY2PageController::createRelativeLink("./css/editor/editor.css")
		));

		$this->addScript("PanelManager.js",array(
			"src" => SOY2PageController::createRelativeLink("./js/cms/PanelManager.js")
		));

		HTMLHead::addLink("form",array(
			"rel" => "stylesheet",
			"type" => "text/css",
			"href" => SOY2PageController::createRelativeLink("./js/cms/PanelManager.css")
		));

		//template保存のボタン追加
		$this->createAdd("save_template_button","HTMLModel",array(
			"id" => "save_template_button",
			"onclick" => "javascript:save_template('".SOY2PageController::createLink("Page.Editor.SaveTemplate." . $page->getId() . "/" . $this->mode)."',this);",
			"visible" => function_exists("json_encode")
		));

		//CSS保存のボタン
		$this->createAdd("save_css_button", "HTMLModel", array(
			"visible" => function_exists("json_encode")
		));

		//フォームの追加
		$this->createAdd("template","HTMLTextArea",array(
			"name" => "template",
			"value" => $template
		));

		$this->createAdd("template_editor","HTMLModel",array(
			"_src"=>SOY2PageController::createRelativeLink("./js/editor/template_editor.html"),
		));

		$this->createAdd("page_detail_form","HTMLForm",array(
			"name" => "main_form"
		));
		//フォームの追加　ここまで

		//ブロック
		$this->createAdd("page_block_info","Block.BlockListPage",array(
			"pageId" => $this->id
		));

		//以下、テンプレートのリンク
		$this->createAdd("blog_template_link_top","HTMLLink",array(
				"link" => SOY2PageController::createLink("Blog.Template.".$this->id.".top"),
		));
		$this->addModel("blog_template_link_top_wrapper",array(
				"class" => ($this->mode == "top") ? "active" : ""
		));
		$this->createAdd("blog_template_link_archive","HTMLLink",array(
				"link" => SOY2PageController::createLink("Blog.Template.".$this->id.".archive"),
		));
		$this->addModel("blog_template_link_archive_wrapper",array(
				"class" => ($this->mode == "archive") ? "active" : ""
		));
		$this->createAdd("blog_template_link_entry","HTMLLink",array(
				"link" => SOY2PageController::createLink("Blog.Template.".$this->id.".entry"),
		));
		$this->addModel("blog_template_link_entry_wrapper",array(
				"class" => ($this->mode == "entry") ? "active" : ""
		));
		$this->createAdd("blog_template_link_popup","HTMLLink",array(
				"link" => SOY2PageController::createLink("Blog.Template.".$this->id.".popup"),
		));
		$this->addModel("blog_template_link_popup_wrapper",array(
				"class" => ($this->mode == "popup") ? "active" : ""
		));

		CMSToolBox::enableFileTree();
		CMSToolBox::addLink(CMSMessageManager::get("SOYCMS_TEMPLATE_HISTORY"),SOY2PageController::createLink("Blog.TemplateHistory.".$this->id.".".$this->mode),true);
		CMSToolBox::addLink(CMSMessageManager::get("SOYCMS_DYNAMIC_EDIT"),SOY2PageController::createLink("Page.Preview.".$this->id),false,"this.target = '_blank'");
		CMSToolBox::addLink(CMSMessageManager::get("SOYCMS_DOWNLOAD_TEMPLATE"),SOY2PageController::createLink("Blog.ExportTemplate.".$this->id.".".$this->mode),false);
		if(CMSUtil::isPageTemplateEnabled()){
			CMSToolBox::addLink(CMSMessageManager::get("SOYCMS_APPLY_WEBPAGE_TEMPLATEPACK"),SOY2PageController::createLink("Blog.ApplyTemplate.".$page->getId().".".$this->mode),true);
		}
		CMSToolBox::addPageJumpBox();
	}
}
