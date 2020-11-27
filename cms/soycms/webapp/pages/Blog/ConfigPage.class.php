<?php

class ConfigPage extends CMSWebPageBase{

	var $id;
	var $page;

	function doPost(){

		if(soy2_check_token()){
			$result = $this->run("Blog.UpdateBlogConfigAction",array(
				"id" => $this->id
			));

			if($result->success()){
				$this->addMessage("BLOG_UPDATE_SUCCESS");
			}else{
				$this->addErrorMessage("BLOG_UPDATE_FAILED");
			}
		}

		$this->jump("Blog.Config.".$this->id);
	}

	function __construct($args) {

		$id = $args[0];
		$this->id = $id;

		parent::__construct();

		//新規作成してから来たときのメッセージ表示
		if(isset($_GET["msg"]) && $_GET["msg"] == "create"){
			$this->addMessage("BLOG_CREATE_SUCCESS");
		}

		$result = $this->run("Blog.DetailAction",array("id"=>$id));
		if(!$result->success()){
			$this->addMessage("PAGE_DETAIL_GET_FAILED");
			$this->jump("Page");
			exit;
		}


		$page = $result->getAttribute("Page");

		$this->addInput("uri", array(
			"value" => $page->getUri(),
			"name" => "uri"
		));

		$this->addLabel("uri_prefix", array(
			"text" => self::getURIPrefix($id)
		));

		//アイコン設定
		$this->addImage("page_icon_show", array(
			"src" => $page->getIconUrl(),
			"onclick" => "javascript:changeImageIcon(".$page->getId().");"
		));

		//ブロック
		$this->createAdd("page_block_info","Block.BlockListPage",array(
			"pageId" => $id
		));

		//見出しに現在編集しているページ名を表示
		$this->addLabel("page_name", array(
			"text" => $page->getTitle()
		));

		$labels = $this->getLabels();

		//ラベルが無いときのメッセージ
		include_once(dirname(__FILE__).'/_LabelBlankPage.class.php');
		$this->createAdd("no_label_message","_LabelBlankPage",array(
			"visible"=>(count($labels) == 0)
		));
		$this->createAdd("no_label_message2","_LabelBlankPage",array(
			"visible"=>(count($labels) == 0)
		));
		$this->addModel("label_exists", array(
			"visible"=>(count($labels) > 0)
		));
		$this->addModel("label_exists2", array(
			"visible"=>(count($labels) > 0)
		));

		/* フォームの部品　開始 */
		$form = $this->create("page_detail_form","CMSFormBase");

		$form->addInput("title", array(
			"value" => $page->getTitle(),
			"name" => "title"
		));

		$form->addTextArea("description", array(
			"name" => "description",
			"text" => $page->getDescription()
		));

		self::setupWYSIWYG();

		$form->addSelect("parent_page", array(
			"selected" => $page->getParentPageId(),
			"options" => $this->getPageList(),
			"indexOrder" => true,
			"name" => "parentPageId"
		));


		$form->addCheckBox("state_draft", array(
			"selected" => !$page->getIsPublished(),
			"name" => "isPublished",
			"value" => 0,
			"label" => CMSMessageManager::get("SOYCMS_DRAFT")
		));
		$form->addCheckBox("state_public", array(
			"selected" => $page->getIsPublished(),
			"name" => "isPublished",
			"value" => 1,
			"label" => CMSMessageManager::get("SOYCMS_PUBLISHED")
		));

		$start = $page->getOpenPeriodStart();
		$end   = $page->getOpenPeriodEnd();


		//公開期間フォームの表示
		$form->addInput("start_date", array(
			"value" => (is_numeric($start)) ? date('Y-m-d H:i:s',$start) : "",
			"name" => "openPeriodStart"
		));
		$form->addInput("end_date", array(
			"value"=>(is_numeric($end)) ? date('Y-m-d H:i:s',$end) : "",
			"name"=>"openPeriodEnd"
		));
		$form->addLabel("open_period_show", array(
			"html" => CMSUtil::getOpenPeriodMessage($start, $end)
		));

		//カテゴリリスト
		//TODO 表示変更
		$form->createAdd("use_label_list","Blog_LabelList",array(
			"list" => $labels,
			"checkedList" => array($page->getBlogLabelId()),
			"name" => "blogLabelId",
			"idBase" => "BlogLabel_"
		));

		$form->createAdd("category_label_list","Blog_LabelList",array(
			"list" => $labels,
			"checkedList" => $page->getCategoryLabelList(),
			"name" => "categoryLabelList[]",
			"idBase" => "CategoryList"
		));



		//ページ生成設定
		$form->addLabel("top_page_uri_prefix", array(
			"text" => (strlen($page->getUri()) > 0) ? "/" . $page->getUri() . "/" : "/"
		));

		$form->addLabel("entry_page_uri_prefix", array(
			"text" => (strlen($page->getUri()) > 0) ? "/" . $page->getUri() . "/" : "/"
		));

		$form->addInput("top_page_uri", array(
			"value" => $page->getTopPageUri(),
			"name"=>"topPageUri",
		));

		$form->addInput("entry_page_uri", array(
			"value" => $page->getEntryPageUri(),
			"name"=>"entryPageUri",
		));

		$form->addLabel("month_page_uri_prefix", array(
			"text" => (strlen($page->getUri())>0) ? "/" . $page->getUri() . "/" : "/"
		));

		$form->addInput("month_page_uri", array(
			"value" => $page->getMonthPageUri(),
			"name"=>"monthPageUri",
		));

		$form->addLabel("category_page_uri_prefix", array(
			"text" => (strlen($page->getUri())>0) ? "/" . $page->getUri() . "/" : "/"
		));

		$form->addInput("category_page_uri", array(
			"value" => $page->getCategoryPageUri(),
			"name"=>"categoryPageUri",
		));

		$form->addLabel("rss_page_uri_prefix", array(
			"text" => (strlen($page->getUri())>0) ? "/" . $page->getUri() . "/" : "/"
		));

		$form->addInput("rss_page_uri", array(
			"value" => $page->getRssPageUri(),
			"name"=>"rssPageUri",
		));

		//表示件数
		$form->addInput("top_display_count", array(
			"value" => $page->getTopDisplayCount(),
			"name"=>"topDisplayCount"
		));
		$form->addInput("month_display_count", array(
			"value" => $page->getMonthDisplayCount(),
			"name"=>"monthDisplayCount"
		));
		$form->addInput("category_display_count", array(
			"value" => $page->getCategoryDisplayCount(),
			"name"=>"categoryDisplayCount"
		));
		$form->addInput("rss_display_count", array(
			"value" => $page->getRssDisplayCount(),
			"name"=>"rssDisplayCount"
		));

		//表示順
		$form->addSelect("top_entry_sort", array(
			"name" => "topEntrySort",
			"options" => array("desc" => "降順", "asc" => "昇順"),
			"selected" => $page->getTopEntrySort()
		));
		$form->addSelect("month_entry_sort", array(
			"name" => "monthEntrySort",
			"options" => array("desc" => "降順", "asc" => "昇順"),
			"selected" => $page->getMonthEntrySort()
		));
		$form->addSelect("category_entry_sort", array(
			"name" => "categoryEntrySort",
			"options" => array("desc" => "降順", "asc" => "昇順"),
			"selected" => $page->getCategoryEntrySort()
		));

		//生成フラグ
		$form->addCheckBox("generateEntryFlag", array(
			"selected"=>$page->getGenerateEntryFlag(),
			"value"=>"1"
		));
		$form->addCheckBox("generateTopFlag", array(
			"selected"=>$page->getGenerateTopFlag(),
			"value"=>"1"
		));
		$form->addCheckBox("generateMonthFlag", array(
			"selected"=>$page->getGenerateMonthFlag(),
			"value"=>"1"
		));
		$form->addCheckBox("generateCategoryFlag", array(
			"selected"=>$page->getGenerateCategoryFlag(),
			"value"=>"1"
		));
		$form->addCheckBox("generateRssFlag", array(
			"selected"=>$page->getGenerateRssFlag(),
			"value"=>"1"
		));


		$form->addInput("top_title_format", array(
			"value"=>$page->getTopTitleFormat(),
			"name"=>"topTitleFormat"
		));

		$form->addInput("entry_title_format", array(
			"value"=>$page->getEntryTitleFormat(),
			"name"=>"entryTitleFormat"
		));

		$form->addInput("month_title_format", array(
			"value"=>$page->getMonthTitleFormat(),
			"name"=>"monthTitleFormat"
		));

		$form->addInput("category_title_format", array(
			"value"=>$page->getCategoryTitleFormat(),
			"name"=>"categoryTitleFormat"
		));

		$form->addInput("feed_title_format", array(
			"value"=>$page->getFeedTitleFormat(),
			"name"=>"feedTitleFormat"
		));

		$this->add("page_detail_form",$form);
		/* フォームの部品　ここまで　*/

		//ブログメニュー
		$this->createAdd("BlogMenu","Blog.BlogMenuPage",array(
			"arguments" => array($id)
		));

		//アイコンリスト
		$this->createAdd("image_list","_component.Label.LabelIconListComponent",array(
			"list" => $this->getLabelIconList()
		));

		$this->addModel("innerLink",array(
			"script" => 'var CreateLabelLink = "'.SOY2PageController::createLink("Entry.CreateNewLabel").'";'
		));

		//b_blockの使用設定
		$bBlockConfig = $page->getBBlockConfig();
		$html = array();
		foreach($page->getBBlockList() as $tag){
			if(isset($bBlockConfig[$tag]) && $bBlockConfig[$tag] == 1){
				$html[] = "<label><input type=\"checkbox\" name=\"bBlockConfig[" . $tag . "]\" value=\"1\" checked=\"checked\">" . $tag . "</label>";
			}else{
				$html[] = "<label><input type=\"checkbox\" name=\"bBlockConfig[" . $tag . "]\" value=\"1\">" . $tag . "</label>";
			}
		}
		//ダミーを追加
		$html[] = "<input type=\"hidden\" name=\"bBlockConfig[dummy]\" value=\"1\">";

		$this->addLabel("b_block_config_checks", array(
			"html" => implode("\n&nbsp;", $html)
		));

		//ラベルの追加
// 		if(count($labels) != 0){
// 			CMSToolBox::addLink(CMSMessageManager::get("SOYCMS_ADD_NEW_LABEL"),"javascript:void(0);",false,"create_label();");
// 		}

		//ラベルリストのCSS
		HTMLHead::addLink("editor.css",array(
			"type" => "text/css",
			"rel" => "stylesheet",
			"href" => SOY2PageController::createRelativeLink("css/entry/editor.css")."?".SOYCMS_BUILD_TIME
		));

		//ブログ設定のCSS
		HTMLHead::addLink("entrytree",array(
			"rel" => "stylesheet",
			"type" => "text/css",
			"href" => SOY2PageController::createRelativeLink("./css/blog/config.css")
		));

		//ツールボックス
		if($page->isActive() == Page::PAGE_ACTIVE){
			$pageUrl = CMSUtil::getSiteUrl() . ( (strlen($page->getUri()) >0) ? $page->getUri() ."/" : "" ) ;

			CMSToolBox::addLink(CMSMessageManager::get("SOYCMS_SHOW_BLOGPAGE"),
				$pageUrl,
				false,
				"this.target = '_blank'"
			);
			CMSToolBox::addLink(CMSMessageManager::get("SOYCMS_SHOW_MONTHLYARCHIVEPAGE"),
				$pageUrl.$page->getMonthPageUri()."/".date("Y/m"),
				false,
				"this.target = '_blank'"
			);

			$categoryLabel = @$labels[array_shift($page->getCategoryLabelList())];
			if(!$categoryLabel)$categoryLabel = new Label();

			CMSToolBox::addLink(CMSMessageManager::get("SOYCMS_SHOW_CATEGORYARCHIVEPAGE"),
				$pageUrl.$page->getCategoryPageUri()."/".rawurlencode($categoryLabel->getAlias()),
				false,
				"this.target = '_blank'"
			);
			CMSToolBox::addLink(CMSMessageManager::get("SOYCMS_SHOW_RSS"),
				$pageUrl.$page->getRssPageUri()."?feed=rss",
				false,
				"this.target = '_blank'"
			);
			CMSToolBox::addLink(CMSMessageManager::get("SOYCMS_SHOW_ATOM"),
				$pageUrl.$page->getRssPageUri()."?feed=atom",
				false,
				"this.target = '_blank'"
			);
		}
		CMSToolBox::addPageJumpBox();

	}

	/**
	 * このページIDに対する呼び出しURIの定型部分を取得
	 */
	private function getURIPrefix($pageId){
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
	 * ラベル一覧を取得
	 */
	function getLabels(){
		$result = $this->run("Label.LabelListAction");

		$labels = $result->getAttribute("list");
		return $labels;
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

			if(!preg_match('/^blog_/',$file))continue;

			$return[] = (object)array(
				"filename" => $file,
				"url" => CMS_PAGE_ICON_DIRECTORY_URL . $file,
			);
		}


		return $return;
	}

	private function setupWYSIWYG(){
		//Call Event
		CMSPlugin::callEventFunc("onBlogSetupWYSIWYG");

		$jsVarsAndPaths = array(
				"InsertLinkAddress" => "Entry.Editor.InsertLink",
				"InsertImagePage" => "Entry.Editor.FileUpload",
				"CreateLabelLink" => "Entry.CreateNewLabel",
				"templateAjaxURL" => "EntryTemplate.GetTemplateAjaxPage",
		);

		//Cookieからエディタのタイプを取得
		$editor = isset($_COOKIE["blog_text_editor"]) ? $_COOKIE["blog_text_editor"] : "plain" ;

		$scriptsArr = array(
				"plain"=> array(
						"./js/editor/PlainTextEditor.js",
						"./js/editor/EntryEditorFunctions.js",
				),
				"tinyMCE" => array(
						"./js/tinymce/tinymce.min.js",
						"./js/editor/RichTextEditor.js",
						"./js/editor/EntryEditorFunctions.js",
				)
		);
		$jsFiles = isset($scriptsArr[$editor]) ? $scriptsArr[$editor] : $scriptsArr["tinyMCE"];

		//bootstrapを使った管理画面用（JavaScriptはファイル末尾で読み込む）
		self::createAddJavaScript($jsVarsAndPaths, $jsFiles);
	}

	/**
	 * 記事編集に必要なJavaScriptをcreateAddで追加する。soy:id=entry_editor_javascripts
	 */
	private function createAddJavaScript($jsVarsAndPaths, $scriptFiles){
		$script = array();
		$script[] = '<script type="text/javascript">'."\n";
		foreach($jsVarsAndPaths as $var => $path){
			$script[] = 'var '.$var.' = "' . htmlspecialchars(SOY2PageController::createLink($path),ENT_QUOTES,"UTF-8") . '";';
		}
		if(SOYCMSEmojiUtil::isInstalled()){
			$script[] = 'var mceSOYCMSEmojiURL = "'.htmlspecialchars(SOYCMSEmojiUtil::getEmojiInputPageUrl().'#tinymce',ENT_QUOTES,"UTF-8").'";';
		}
		$script[] = '</script>'."\n";

		foreach($scriptFiles as $path){
			$script[] = '<script type="text/javascript" src="'.htmlspecialchars(SOY2PageController::createRelativeLink($path)."?".SOYCMS_BUILD_TIME,ENT_QUOTES,"UTF-8").'"></script>';
		}
		//ラベルのチェック状況を調べる
		$script[] = '<script type="text/javascript">';
		$script[] = "$('input[name=\"label[]\"]').each(function(ele){";
		$script[] = "	toggle_labelmemo(\$(this).val(), \$(this).is(\":checked\"));";
		$script[] = "});";
		$script[] = '</script>'."\n";

		$this->addLabel("entry_editor_javascripts", array(
			"html" => implode("\n", $script),
		));
	}
}

class Blog_LabelList extends HTMLList{

	private $checkedList;
	private $name;
	private $idBase;

	public function setCheckedList($list){
		$this->checkedList = $list;
	}

	public function setName($name){
		$this->name = $name;
	}

	public function setIdBase($idBase){
		$this->idBase = $idBase;
	}

	protected function populateItem($entity){
		$elementID = $this->idBase.$entity->getId();

		$this->createAdd("label_check","HTMLCheckBox",array(
			"name"	  => $this->name,
			"value"	 => $entity->getId(),
			"selected"  => in_array($entity->getId(),$this->checkedList),
			"elementId" => $elementID,
		));
		$this->createAdd("label_label","HTMLModel",array(
			"for" => $elementID,
		));
		$this->createAdd("label_caption","HTMLLabel",array(
			"text" => $entity->getCaption(),
			"style"=> "color:#" . sprintf("%06X",$entity->getColor()).";"
					 ."background-color:#" . sprintf("%06X",$entity->getBackgroundColor()).";"
		));

		$this->createAdd("label_icon","HTMLImage",array(
			"src" => $entity->getIconUrl()
		));

	}
}
